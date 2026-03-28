<?php

declare(strict_types=1);

namespace App\Services;

use Core\Orm\Models;
use Core\View\View;
use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;
use RuntimeException;

/**
 * Dosya yukleme, mail render etme ve mail gonderimi icin ortak yardimci servis.
 */
final class GlobalTools
{
    /**
     * @param string $mailLogFile Giden mail etkinligi icin log dosyasi.
     * @param FileUploadService $uploads Dosya yukleme servisi.
     * @param array $mailConfig Mail tasima yapilandirmasi.
     * @param View $siteMailView Site mail sablonu goruntuleyicisi.
     * @param View $adminMailView Admin mail sablonu goruntuleyicisi.
     */
    public function __construct(
        private readonly string $mailLogFile,
        private readonly FileUploadService $uploads,
        private readonly array $mailConfig,
        private readonly View $siteMailView,
        private readonly View $adminMailView
    ) {
        $this->ensureDirectory(dirname($this->mailLogFile));
    }

    /**
     * Yuklenen dosyayi yapilandirilmis herkese acik yukleme dizinine tasir.
     *
     * @param array $file PHP'den gelen yuklenen dosya dizisi.
     * @param string $directory Yukleme kok dizini altindaki alt klasor.
     * @param array $options Izin verilen uzantilar ve azami boyut gibi yukleme kisitlari.
     * @return array
     */
    public function uploadFile(array $file, string $directory = 'common', array $options = []): array
    {
        return $this->uploads->uploadFile($file, $directory, $options);
    }

    /**
     * Yapilandirilmis mail tasiyicisini kullanarak mail gonderir.
     *
     * @param string|array $to Alici ya da alicilar.
     * @param string $subject Mesaj konusu.
     * @param string $message Mesaj govdesi.
     * @param array $headers Opsiyonel reply-to, cc, bcc ve content-type ipuclari.
     * @return bool
     */
    public function sendMail(string|array $to, string $subject, string $message, array $headers = []): bool
    {
        $recipients = is_array($to) ? $to : [$to];
        $mailer = $this->buildMailer($headers);

        try {
            foreach ($recipients as $recipient) {
                $mailer->addAddress($recipient);
            }

            $mailer->Subject = $subject;
            $mailer->Body = $message;
            $mailer->AltBody = strip_tags($message);
            $mailer->isHTML($this->isHtmlMessage($headers, $message));

            $result = $mailer->send();
            $this->logMail($recipients, $subject, $message, $result ? 'sent' : 'failed');

            return $result;
        } catch (MailException $exception) {
            $this->logMail($recipients, $subject, $message, 'failed', $exception->getMessage());

            throw new RuntimeException('Mail could not be sent: ' . $exception->getMessage(), 0, $exception);
        }
    }

    /**
     * Verilen kanal icin mail sablonunu render eder.
     *
     * @param string $channel site ya da admin gibi sablon kanal adi.
     * @param string $template Sablon adi.
     * @param array $data Sablon verisi.
     * @param string|null $layout Opsiyonel layout ezmesi.
     * @return string
     */
    public function renderMailTemplate(string $channel, string $template, array $data = [], ?string $layout = null): string
    {
        return $this->mailView($channel)->render($template, $data, $layout);
    }

    /**
     * HTML mail sablonunu render eder ve gonderir.
     *
     * @param string $channel Sablon kanal adi.
     * @param string|array $to Alici ya da alicilar.
     * @param string $subject Mesaj konusu.
     * @param string $template Sablon adi.
     * @param array $data Sablon verisi.
     * @param array $headers Ek mail header'lari.
     * @param string|null $layout Opsiyonel layout ezmesi.
     * @return bool
     */
    public function sendTemplatedMail(
        string $channel,
        string|array $to,
        string $subject,
        string $template,
        array $data = [],
        array $headers = [],
        ?string $layout = null
    ): bool {
        $html = $this->renderMailTemplate($channel, $template, $data, $layout);

        return $this->sendMail($to, $subject, $html, array_merge([
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/html; charset=UTF-8',
        ], $headers));
    }

    /**
     * Yuklenen dosya ya da klasor icin herkese acik URL yolu olusturur.
     *
     * @param string $directory Yukleme dizini adi.
     * @param string $fileName Opsiyonel dosya adi.
     * @return string
     */
    public function publicUploadPath(string $directory = 'common', string $fileName = ''): string
    {
        return $this->uploads->publicUploadPath($directory, $fileName);
    }

    /**
     * Bir kanal icin dogru mail view goruntuleyicisini cozer.
     *
     * @param string $channel Sablon kanal adi.
     * @return View
     */
    private function mailView(string $channel): View
    {
        return match ($channel) {
            'site' => $this->siteMailView,
            'admin' => $this->adminMailView,
            default => throw new RuntimeException("Unknown mail channel: {$channel}"),
        };
    }

    /**
     * Proje ayarlarindan PHPMailer nesnesi olusturur ve ayarlar.
     *
     * @param array $headers Ek mail header'lari.
     * @return PHPMailer
     */
    private function buildMailer(array $headers): PHPMailer
    {
        $mailer = new PHPMailer(true);
        $mailer->CharSet = 'UTF-8';
        $mailer->Timeout = (int) ($this->mailConfig['timeout'] ?? 15);
        $mailer->SMTPDebug = (int) ($this->mailConfig['debug'] ?? 0);

        $transport = (string) ($this->mailConfig['mailer'] ?? 'smtp');

        if ($transport === 'smtp') {
            $mailer->isSMTP();
            $mailer->Host = (string) ($this->mailConfig['host'] ?? '');
            $mailer->Port = (int) ($this->mailConfig['port'] ?? 587);
            $mailer->SMTPAuth = (bool) ($this->mailConfig['auth'] ?? true);
            $mailer->Username = (string) ($this->mailConfig['username'] ?? '');
            $mailer->Password = (string) ($this->mailConfig['password'] ?? '');

            $encryption = (string) ($this->mailConfig['encryption'] ?? 'tls');
            if ($encryption !== '') {
                $mailer->SMTPSecure = $encryption;
            }
        } else {
            $mailer->isMail();
        }

        $fromEmail = (string) ($this->mailConfig['from_email'] ?? 'noreply@example.com');
        $fromName = (string) ($this->mailConfig['from_name'] ?? 'MiU');
        $mailer->setFrom($fromEmail, $fromName);

        $replyTo = (string) ($this->mailConfig['reply_to'] ?? '');
        if ($replyTo !== '') {
            $mailer->addReplyTo($replyTo, (string) ($this->mailConfig['reply_to_name'] ?? ''));
        }

        foreach ($headers as $name => $value) {
            if (! is_string($value)) {
                continue;
            }

            if (strtolower($name) === 'reply-to') {
                $mailer->addReplyTo($value);
                continue;
            }

            if (strtolower($name) === 'cc') {
                foreach (array_map('trim', explode(',', $value)) as $address) {
                    if ($address !== '') {
                        $mailer->addCC($address);
                    }
                }
                continue;
            }

            if (strtolower($name) === 'bcc') {
                foreach (array_map('trim', explode(',', $value)) as $address) {
                    if ($address !== '') {
                        $mailer->addBCC($address);
                    }
                }
            }
        }

        return $mailer;
    }

    /**
     * Giden mesaj govdesinin HTML olarak ele alinip alinmayacagini belirler.
     *
     * @param array $headers Mail header'lari.
     * @param string $message Mesaj govdesi.
     * @return bool
     */
    private function isHtmlMessage(array $headers, string $message): bool
    {
        $contentType = strtolower((string) ($headers['Content-Type'] ?? ''));

        if (str_contains($contentType, 'text/html')) {
            return true;
        }

        return $message !== strip_tags($message);
    }

    /**
     * Yerel log dosyasina mail gonderim kaydi ekler.
     *
     * @param array $recipients Alici listesi.
     * @param string $subject Mesaj konusu.
     * @param string $message Mesaj govdesi.
     * @param string $status Teslim durumu.
     * @param string|null $error Opsiyonel hata mesaji.
     */
    private function logMail(array $recipients, string $subject, string $message, string $status, ?string $error = null): void
    {
        $timestamp = time();
        $payload = sprintf(
            "[%s] STATUS: %s | TO: %s | SUBJECT: %s | ERROR: %s\n%s\n\n",
            date('Y-m-d H:i:s'),
            $status,
            implode(', ', $recipients),
            $subject,
            $error ?? '-',
            $message
        );

        file_put_contents($this->mailLogFile, $payload, FILE_APPEND);

        $mailLog = Models::get('mail_logs');
        $mailLog->channel = 'global';
        $mailLog->recipient = implode(', ', $recipients);
        $mailLog->subject = $subject;
        $mailLog->template_name = '';
        $mailLog->status = $status;
        $mailLog->response_message = $error ?? '';
        $mailLog->created_at = $timestamp;
        $mailLog->save();
    }

    /**
     * Dosya islemlerinden once dizinin var oldugundan emin olur.
     *
     * @param string $path Dizin yolu.
     */
    private function ensureDirectory(string $path): void
    {
        if (! is_dir($path)) {
            mkdir($path, 0777, true);
        }
    }
}
