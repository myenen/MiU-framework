<?php

declare(strict_types=1);

namespace Core\Orm;

use AllowDynamicProperties;
use PDO;
use PDOStatement;
use stdClass;
use RuntimeException;

/**
 * Hafif aktif-kayit tarzi model ve sorgu kurucu temel sinifi.
 */
class Models
{
    protected static ?PDO $db = null;
    protected static string $driver = 'sqlite';
    protected static string $schema = 'public';
    protected static array $modelCacheConfig = [
        'enabled' => false,
        'refresh' => false,
        'path' => '',
        'namespace' => 'default',
    ];
    protected array $originalAttributes = [];
    protected array $queryWheres = [];
    protected array $queryOrders = [];
    protected ?int $queryLimit = null;
    protected ?int $queryOffset = null;
    protected array $querySelect = ['*'];

    /**
     * Model kullanilmadan once veritabani baglantisinin hazir oldugunu dogrular.
     */
    public function __construct()
    {
        self::db();
    }

    /**
     * Tum model nesnelerinin kullanacagi ortak PDO baglantisini kaydeder.
     *
     * @param PDO $pdo Aktif PDO baglantisi.
     * @param string|null $modelsPath Geriye donuk uyumluluk icin saklanmistir.
     * @return void
     */
    public static function setDb(PDO $pdo, ?string $modelsPath = null): void
    {
        self::$db = $pdo;
        self::$driver = (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    /**
     * PostgreSQL tablo incelemesi icin varsayilan schema adini ayarlar.
     *
     * @param string $schema Schema adi.
     * @return void
     */
    public static function setSchema(string $schema): void
    {
        self::$schema = $schema;
    }

    /**
     * Model metadata cache davranisini ayarlar.
     *
     * @param array<string, mixed> $config Cache ayarlari.
     * @return void
     */
    public static function setModelCacheConfig(array $config): void
    {
        self::$modelCacheConfig = [
            'enabled' => (bool) ($config['enabled'] ?? false),
            'refresh' => (bool) ($config['refresh'] ?? false),
            'path' => (string) ($config['path'] ?? ''),
            'namespace' => (string) ($config['namespace'] ?? 'default'),
        ];
    }

    /**
     * Ortak PDO baglantisini dondurur.
     *
     * @return PDO Aktif PDO baglantisi.
     */
    public static function db(): PDO
    {
        if (! self::$db instanceof PDO) {
            throw new RuntimeException('Database connection is not initialized for Models.');
        }

        return self::$db;
    }

    /**
     * Tablo adindan genel bir model nesnesi olusturur.
     *
     * @param string $name Tablo adi.
     * @return object Tablo sutunlariyla doldurulmus genel model nesnesi.
     */
    public static function get(string $name): object
    {
        $table = self::sanitizeIdentifier($name);
        $columns = self::columnsForModel($table);
        $model = new GenericModel();

        foreach ($columns as $column) {
            $property = $column['name'];
            $model->{$property} = $property === 'id' ? null : '';
        }

        $model->table = $table;
        $model->syncOriginalAttributes();

        return $model;
    }

    /**
     * Model olusturmak icin tablo sutunlarini cache veya veritabanindan cozer.
     *
     * @param string $table Tablo adi.
     * @return array<int, array{name: string, type: string}>
     */
    protected static function columnsForModel(string $table): array
    {
        $cached = self::loadColumnsFromCache($table);

        if ($cached !== null) {
            return $cached;
        }

        $columns = self::describeTable($table);
        self::writeColumnsToCache($table, $columns);

        return $columns;
    }

    /**
     * Mevcut model verisini veritabanina ekler.
     *
     * @return object|int|string Basariliysa eklenen kaydin id degeri, hatada hata nesnesi.
     */
    public function save(): object|int|string
    {
        $db = self::db();
        $table = $this->tableName();
        $fields = [];
        $placeholders = [];
        $params = [];

        $this->touchTimestampsForInsert();

        foreach ($this->modelAttributes() as $key => $value) {
            if (in_array($key, ['table', 'id'], true)) {
                continue;
            }

            if ($value === '' || $value === null) {
                continue;
            }

            $fields[] = self::quoteIdentifier($key);
            $placeholders[] = ':' . $key;
            $params[':' . $key] = $value;
        }

        if ($fields === []) {
            return (object) ['error' => true, 'msg' => 'Kaydedilecek veri yok'];
        }

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            self::quoteIdentifier($table),
            implode(', ', $fields),
            implode(', ', $placeholders)
        );

        $stmt = $db->prepare($sql);
        if (! $stmt instanceof PDOStatement) {
            return (object) ['error' => true, 'msg' => 'Prepare hatasi'];
        }

        self::bindParams($stmt, $params);

        if (! $stmt->execute()) {
            return (object) ['error' => true, 'msg' => $stmt->errorInfo()[2] ?? 'Execute hatasi'];
        }

        $lastId = $db->lastInsertId();

        if ($lastId === '0' && self::$driver === 'pgsql') {
            $sequence = sprintf('%s_id_seq', $table);
            $lastId = $db->lastInsertId($sequence);
        }

        $this->id = $lastId;
        $this->syncOriginalAttributes();

        return $lastId;
    }

    /**
     * Mevcut modeli id alanina gore gunceller.
     *
     * @return object Islem sonuc nesnesi.
     */
    public function update(): object
    {
        $db = self::db();
        $table = $this->tableName();
        $id = $this->id ?? null;

        if ($id === null || $id === '') {
            return (object) ['error' => true, 'msg' => 'Guncellenecek kayit ID eksik'];
        }

        $sets = [];
        $params = [];

        $this->touchTimestampsForUpdate();

        foreach ($this->dirtyAttributes() as $key => $value) {
            if (in_array($key, ['table', 'id'], true)) {
                continue;
            }

            if ($value === null) {
                continue;
            }

            $sets[] = sprintf('%s = :%s', self::quoteIdentifier($key), $key);
            $params[':' . $key] = $value;
        }

        if ($sets === []) {
            return (object) ['error' => true, 'msg' => 'Guncellenecek alan yok'];
        }

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s = :id',
            self::quoteIdentifier($table),
            implode(', ', $sets),
            self::quoteIdentifier('id')
        );
        $params[':id'] = $id;

        $stmt = $db->prepare($sql);
        if (! $stmt instanceof PDOStatement) {
            return (object) ['error' => true, 'msg' => 'Prepare hatasi'];
        }

        self::bindParams($stmt, $params);

        if (! $stmt->execute()) {
            return (object) ['error' => true, 'msg' => $stmt->errorInfo()[2] ?? 'Execute hatasi'];
        }

        $this->syncOriginalAttributes();

        return (object) ['error' => false, 'msg' => 'Islem basarili'];
    }

    /**
     * Verilen alana ya da model id degerine gore kaydi siler.
     *
     * @param mixed $find Eslesecek deger ya da mevcut model id degerini kullanmak icin "id".
     * @param string $field Silme kosulunda kullanilan alan adi.
     * @return object Islem sonuc nesnesi.
     */
    public function delete(mixed $find = 'id', string $field = 'id'): object
    {
        $db = self::db();
        $table = $this->tableName();
        $value = $find === 'id' ? ($this->id ?? null) : $find;

        if ($value === null) {
            return (object) ['error' => true, 'msg' => 'Silinecek deger eksik'];
        }

        $sql = sprintf(
            'DELETE FROM %s WHERE %s = :val',
            self::quoteIdentifier($table),
            self::quoteIdentifier($field)
        );

        $stmt = $db->prepare($sql);
        if (! $stmt instanceof PDOStatement) {
            return (object) ['error' => true, 'msg' => 'Prepare hatasi'];
        }

        $stmt->bindValue(':val', $value, self::getPdoType($value));

        if (! $stmt->execute()) {
            return (object) ['error' => true, 'msg' => $stmt->errorInfo()[2] ?? 'Execute hatasi'];
        }

        return (object) ['error' => false, 'msg' => 'Islem basarili'];
    }

    /**
     * Opsiyonel parametrelerle ham SQL sorgusu calistirir.
     *
     * @param string $sql SQL ifadesi.
     * @param array<string, mixed> $params Sorgu parametreleri.
     * @return array<int, object>|false|object Sonuc satirlari, bossa false ya da hata nesnesi.
     */
    public function runSQL(string $sql, array $params = []): array|false|object
    {
        $stmt = self::db()->prepare($sql);

        if (! $stmt instanceof PDOStatement) {
            return (object) ['error' => true, 'msg' => 'Prepare hatasi'];
        }

        self::bindParams($stmt, $params);

        if (! $stmt->execute()) {
            return (object) ['error' => true, 'msg' => $stmt->errorInfo()[2] ?? 'Execute hatasi'];
        }

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($rows === []) {
            return false;
        }

        return array_map(static fn (array $row): object => (object) $row, $rows);
    }

    /**
     * Tek bir alana gore ve istege bagli LIKE aramasi ile kayit bulur.
     *
     * @param mixed $find Aranacak deger.
     * @param string $field Arama yapilacak alan.
     * @param string $orderField Siralama icin kullanilacak alan.
     * @param string $order Siralama yonu.
     * @param bool $like LIKE karsilastirmasi kullanilip kullanilmayacagi.
     * @return object|array|false Doldurulmus sonuc kumesi.
     */
    public function find(mixed $find, string $field = 'id', string $orderField = 'id', string $order = 'DESC', bool $like = false): object|array|false
    {
        $value = $like ? '%' . $find . '%' : $find;

        return $this
            ->resetQuery()
            ->where($field, $like ? 'LIKE' : '=', $value)
            ->orderBy($orderField, $order)
            ->all();
    }

    /**
     * Sorgu icin secilecek sutunlari belirler.
     *
     * @param array<int, string>|string $columns Secilecek sutunlar.
     * @return static Kopyalanmis sorgu nesnesi.
     */
    public function select(array|string $columns = ['*']): static
    {
        $clone = clone $this;
        $clone->querySelect = is_array($columns) ? $columns : [$columns];

        return $clone;
    }

    /**
     * Sorguya AND where kosulu ekler.
     *
     * @param string $field Sutun adi.
     * @param string|int|float|bool|null $operatorOrValue Operator ya da dogrudan karsilastirma degeri.
     * @param mixed $value Operator verildiginde kullanilacak karsilastirma degeri.
     * @return static Kopyalanmis sorgu nesnesi.
     */
    public function where(string $field, string|int|float|bool|null $operatorOrValue, mixed $value = null): static
    {
        $clone = clone $this;
        $operator = '=';
        $compareValue = $operatorOrValue;

        if ($value !== null || in_array((string) $operatorOrValue, ['=', '!=', '<>', '>', '>=', '<', '<=', 'LIKE'], true)) {
            $operator = strtoupper((string) $operatorOrValue);
            $compareValue = $value;
        }

        $clone->queryWheres[] = [
            'boolean' => 'AND',
            'field' => $field,
            'operator' => $operator,
            'value' => $compareValue,
        ];

        return $clone;
    }

    /**
     * Sorguya OR where kosulu ekler.
     *
     * @param string $field Sutun adi.
     * @param string|int|float|bool|null $operatorOrValue Operator ya da dogrudan karsilastirma degeri.
     * @param mixed $value Operator verildiginde kullanilacak karsilastirma degeri.
     * @return static Kopyalanmis sorgu nesnesi.
     */
    public function orWhere(string $field, string|int|float|bool|null $operatorOrValue, mixed $value = null): static
    {
        $clone = clone $this;
        $operator = '=';
        $compareValue = $operatorOrValue;

        if ($value !== null || in_array((string) $operatorOrValue, ['=', '!=', '<>', '>', '>=', '<', '<=', 'LIKE'], true)) {
            $operator = strtoupper((string) $operatorOrValue);
            $compareValue = $value;
        }

        $clone->queryWheres[] = [
            'boolean' => 'OR',
            'field' => $field,
            'operator' => $operator,
            'value' => $compareValue,
        ];

        return $clone;
    }

    /**
     * Verilen alan icin LIKE kosulu ekler.
     *
     * @param string $field Sutun adi.
     * @param string $value Aranacak deger.
     * @return static Kopyalanmis sorgu nesnesi.
     */
    public function whereLike(string $field, string $value): static
    {
        return $this->where($field, 'LIKE', '%' . $value . '%');
    }

    /**
     * Verilen alan icin IN kosulu ekler.
     *
     * @param string $field Sutun adi.
     * @param array<int, mixed> $values Izin verilen degerler.
     * @return static Kopyalanmis sorgu nesnesi.
     */
    public function whereIn(string $field, array $values): static
    {
        $clone = clone $this;
        $clone->queryWheres[] = [
            'boolean' => 'AND',
            'field' => $field,
            'operator' => 'IN',
            'value' => array_values($values),
        ];

        return $clone;
    }

    /**
     * ORDER BY bolumu ekler.
     *
     * @param string $field Sutun adi.
     * @param string $direction Siralama yonu.
     * @return static Kopyalanmis sorgu nesnesi.
     */
    public function orderBy(string $field, string $direction = 'ASC'): static
    {
        $clone = clone $this;
        $clone->queryOrders[] = [
            'field' => $field,
            'direction' => strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC',
        ];

        return $clone;
    }

    /**
     * Sorguya limit ve ofset degerlerini uygular.
     *
     * @param int $limit Maksimum satir adedi.
     * @param int $offset Baslangic ofset degeri.
     * @return static Kopyalanmis sorgu nesnesi.
     */
    public function limit(int $limit, int $offset = 0): static
    {
        $clone = clone $this;
        $clone->queryLimit = $limit;
        $clone->queryOffset = $offset;

        return $clone;
    }

    /**
     * Eslesen ilk kaydi dondurur.
     *
     * @return object|false Ilk doldurulmus kayit ya da bossa false.
     */
    public function first(): object|false
    {
        $result = $this->limit(1)->all();

        if ($result === false) {
            return false;
        }

        if (is_array($result)) {
            return $result[0] ?? false;
        }

        return $result;
    }

    /**
     * Olusturulan select sorgusunu calistirir ve sonucu modele doldurur.
     *
     * @return object|array|false Doldurulmus sonuc kumesi.
     */
    public function all(): object|array|false
    {
        $db = self::db();
        [$sql, $params] = $this->buildSelectQuery();
        $stmt = $db->prepare($sql);

        if (! $stmt instanceof PDOStatement) {
            return false;
        }

        self::bindParams($stmt, $params);

        if (! $stmt->execute()) {
            return false;
        }

        $result = $this->hydrateResult($stmt->fetchAll(PDO::FETCH_OBJ));
        $this->resetQuery();

        return $result;
    }

    /**
     * Mevcut sorgu kosullarina uyan satirlari sayar.
     *
     * @return int Eslesen satir sayisi.
     */
    public function count(): int
    {
        $db = self::db();
        [$sql, $params] = $this->buildAggregateQuery('COUNT(*) AS aggregate_count');
        $stmt = $db->prepare($sql);

        if (! $stmt instanceof PDOStatement) {
            return 0;
        }

        self::bindParams($stmt, $params);

        if (! $stmt->execute()) {
            return 0;
        }

        $count = $stmt->fetchColumn();
        $this->resetQuery();

        return (int) $count;
    }

    /**
     * Mevcut sorguya uyan en az bir kayit olup olmadigini kontrol eder.
     *
     * @return bool Kayit varsa true.
     */
    public function exists(): bool
    {
        return $this->count() > 0;
    }

    /**
     * Tek bir sutundaki degerleri duz bir dizi olarak dondurur.
     *
     * @param string $column Sutun adi.
     * @return array<int, mixed> Sutun degerleri.
     */
    public function pluck(string $column): array
    {
        $db = self::db();
        $query = $this->select([$column]);
        [$sql, $params] = $query->buildSelectQuery();
        $stmt = $db->prepare($sql);

        if (! $stmt instanceof PDOStatement) {
            return [];
        }

        self::bindParams($stmt, $params);

        if (! $stmt->execute()) {
            return [];
        }

        $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $query->resetQuery();

        return is_array($result) ? $result : [];
    }

    /**
     * Sorgu sonuclarini sayfalama metadatasi ile birlikte dondurur.
     *
     * @param int $page Mevcut sayfa numarasi.
     * @param int $perPage Sayfa basina kayit adedi.
     * @return object Veri ve meta alanlarini iceren sayfalama sonucu.
     */
    public function paginate(int $page = 1, int $perPage = 20): object
    {
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $baseQuery = clone $this;
        $total = $baseQuery->count();
        $items = $this->limit($perPage, ($page - 1) * $perPage)->all();
        $itemsArray = $items === false ? [] : (is_array($items) ? $items : [$items]);
        $lastPage = max(1, (int) ceil($total / $perPage));

        return (object) [
            'data' => $itemsArray,
            'meta' => (object) [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => $lastPage,
                'from' => $total === 0 ? 0 : (($page - 1) * $perPage) + 1,
                'to' => $total === 0 ? 0 : min($page * $perPage, $total),
            ],
        ];
    }

    /**
     * Mevcut model nesnesini diziye cevirir.
     *
     * @return array<string, mixed> Model ozellikleri.
     */
    public function toarray(): array
    {
        return $this->modelAttributes();
    }

    /**
     * Sadece izin verilen alanlari iceren bir kopya dondurur.
     *
     * @param array<int, string> $allowed Izin verilen ozellik adlari.
     * @return object Filtrelenmis model kopyasi.
     */
    public function filter(array $allowed = []): object
    {
        $model = clone $this;

        foreach ($model->modelAttributes() as $key => $value) {
            if (! in_array($key, $allowed, true)) {
                unset($model->{$key});
            }
        }

        return $model;
    }

    /**
     * Mevcut modeli kaynak nesnedeki degerlerle doldurur.
     *
     * @param object $data Kaynak nesne.
     * @return object Mevcut model nesnesi.
     */
    public function fill(object $data): object
    {
        foreach ($this->modelAttributes() as $key => $value) {
            if (property_exists($data, $key)) {
                $this->{$key} = $data->{$key};
            }
        }

        return $this;
    }

    /**
     * Yalnizca tabloya ait model alanlarini dondurur.
     *
     * @return array<string, mixed> Icerik alanlari.
     */
    protected function modelAttributes(): array
    {
        $attributes = get_object_vars($this);

        unset(
            $attributes['originalAttributes'],
            $attributes['queryWheres'],
            $attributes['queryOrders'],
            $attributes['queryLimit'],
            $attributes['queryOffset'],
            $attributes['querySelect']
        );

        return $attributes;
    }

    /**
     * Mevcut model icin temizlenmis tablo adini dondurur.
     *
     * @return string Tablo adi.
     */
    protected function tableName(): string
    {
        $table = $this->table ?? null;

        if (! is_string($table) || $table === '') {
            throw new RuntimeException('Model table name is missing.');
        }

        return self::sanitizeIdentifier($table);
    }

    /**
     * Kayit ekleme oncesinde zaman damgalarini otomatik doldurur.
     *
     * @return void
     */
    protected function touchTimestampsForInsert(): void
    {
        $timestamp = time();

        if (property_exists($this, 'created_at') && ($this->created_at === '' || $this->created_at === null)) {
            $this->created_at = $timestamp;
        }

        if (property_exists($this, 'updated_at') && ($this->updated_at === '' || $this->updated_at === null)) {
            $this->updated_at = $timestamp;
        }
    }

    /**
     * Kayit guncelleme oncesinde guncelleme zaman damgasini yeniler.
     *
     * @return void
     */
    protected function touchTimestampsForUpdate(): void
    {
        if (property_exists($this, 'updated_at')) {
            $this->updated_at = time();
        }
    }

    /**
     * Mevcut alanlari temiz referans durum olarak kaydeder.
     *
     * @return void
     */
    protected function syncOriginalAttributes(): void
    {
        $this->originalAttributes = $this->modelAttributes();
    }

    /**
     * Son referans durumdan farkli olan alanlari dondurur.
     *
     * @return array<string, mixed> Degisen alanlar.
     */
    protected function dirtyAttributes(): array
    {
        $current = $this->modelAttributes();
        $dirty = [];

        foreach ($current as $key => $value) {
            if (! array_key_exists($key, $this->originalAttributes) || $value !== $this->originalAttributes[$key]) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    /**
     * Ham sorgu satirlarini doldurulmus model nesnelerine donusturur.
     *
     * @param array<int, object> $models Ham sorgu satirlari.
     * @return object|array|false Doldurulmus sonuc kumesi.
     */
    protected function hydrateResult(array $models): object|array|false
    {
        if ($models === []) {
            return false;
        }

        if (count($models) === 1) {
            $clone = clone $this;
            $clone->fill($models[0]);
            $clone->syncOriginalAttributes();

            return $clone;
        }

        return array_map(function (object $row): object {
            $clone = clone $this;
            $clone->fill($row);
            $clone->syncOriginalAttributes();

            return $clone;
        }, $models);
    }

    /**
     * Aktif sorgu icin SELECT SQL ifadesini ve bagli parametreleri olusturur.
     *
     * @return array{0: string, 1: array<string, mixed>} Sorgu SQL ifadesi ve parametreleri.
     */
    protected function buildSelectQuery(): array
    {
        $table = $this->tableName();
        $params = [];
        $whereSql = '';
        $selectSql = $this->buildSelectColumns();
        $orderSql = $this->buildOrderClause();
        $limitSql = $this->buildLimitClause($params);

        if ($this->queryWheres !== []) {
            $chunks = [];

            foreach ($this->queryWheres as $index => $where) {
                $prefix = $index === 0 ? '' : ' ' . $where['boolean'] . ' ';
                $fieldSql = self::quoteIdentifier($where['field']);

                if ($where['operator'] === 'IN') {
                    $placeholders = [];

                    foreach ($where['value'] as $valueIndex => $value) {
                        $placeholder = ':where_' . $index . '_' . $valueIndex;
                        $placeholders[] = $placeholder;
                        $params[$placeholder] = $value;
                    }

                    if ($placeholders === []) {
                        $chunks[] = $prefix . '1 = 0';
                        continue;
                    }

                    $chunks[] = $prefix . sprintf(
                        '%s IN (%s)',
                        $fieldSql,
                        implode(', ', $placeholders)
                    );
                    continue;
                }

                $placeholder = ':where_' . $index;
                $chunks[] = $prefix . sprintf('%s %s %s', $fieldSql, $where['operator'], $placeholder);
                $params[$placeholder] = $where['value'];
            }

            $whereSql = ' WHERE ' . implode('', $chunks);
        }

        $sql = sprintf(
            'SELECT %s FROM %s%s%s%s',
            $selectSql,
            self::quoteIdentifier($table),
            $whereSql,
            $orderSql,
            $limitSql
        );

        return [$sql, $params];
    }

    /**
     * Mevcut WHERE kosullarini kullanarak aggregate sorgu olusturur.
     *
     * @param string $aggregateSelect Aggregate ifadesi.
     * @return array{0: string, 1: array<string, mixed>} Sorgu SQL ifadesi ve parametreleri.
     */
    protected function buildAggregateQuery(string $aggregateSelect): array
    {
        $table = $this->tableName();
        $params = [];
        $whereSql = '';

        if ($this->queryWheres !== []) {
            $chunks = [];

            foreach ($this->queryWheres as $index => $where) {
                $prefix = $index === 0 ? '' : ' ' . $where['boolean'] . ' ';
                $fieldSql = self::quoteIdentifier($where['field']);

                if ($where['operator'] === 'IN') {
                    $placeholders = [];

                    foreach ($where['value'] as $valueIndex => $value) {
                        $placeholder = ':where_' . $index . '_' . $valueIndex;
                        $placeholders[] = $placeholder;
                        $params[$placeholder] = $value;
                    }

                    if ($placeholders === []) {
                        $chunks[] = $prefix . '1 = 0';
                        continue;
                    }

                    $chunks[] = $prefix . sprintf('%s IN (%s)', $fieldSql, implode(', ', $placeholders));
                    continue;
                }

                $placeholder = ':where_' . $index;
                $chunks[] = $prefix . sprintf('%s %s %s', $fieldSql, $where['operator'], $placeholder);
                $params[$placeholder] = $where['value'];
            }

            $whereSql = ' WHERE ' . implode('', $chunks);
        }

        return [
            sprintf('SELECT %s FROM %s%s', $aggregateSelect, self::quoteIdentifier($table), $whereSql),
            $params,
        ];
    }

    /**
     * Secilen sutunlar icin SQL parcasini olusturur.
     *
     * @return string SELECT sutun parcasi.
     */
    protected function buildSelectColumns(): string
    {
        if ($this->querySelect === ['*']) {
            return '*';
        }

        return implode(', ', array_map(static fn (string $column): string => self::quoteIdentifier($column), $this->querySelect));
    }

    /**
     * ORDER BY SQL parcasini olusturur.
     *
     * @return string ORDER BY parcasi ya da bos metin.
     */
    protected function buildOrderClause(): string
    {
        if ($this->queryOrders === []) {
            return '';
        }

        $parts = array_map(static function (array $order): string {
            return sprintf('%s %s', self::quoteIdentifier($order['field']), $order['direction']);
        }, $this->queryOrders);

        return ' ORDER BY ' . implode(', ', $parts);
    }

    /**
     * Aktif veritabani surucusu icin LIMIT/OFFSET SQL parcasini olusturur.
     *
     * @param array<string, mixed> $params Limit baglamalariyla doldurulacak parametre dizisi.
     * @return string LIMIT bolumu ya da bos metin.
     */
    protected function buildLimitClause(array &$params): string
    {
        if ($this->queryLimit === null) {
            return '';
        }

        $params[':limitps'] = $this->queryLimit;
        $params[':limitoffset'] = $this->queryOffset ?? 0;

        return ' ' . self::limitClause();
    }

    /**
     * Mevcut nesnedeki gecici sorgu kurucu durumunu temizler.
     *
     * @return static Mevcut model nesnesi.
     */
    protected function resetQuery(): static
    {
        $this->queryWheres = [];
        $this->queryOrders = [];
        $this->queryLimit = null;
        $this->queryOffset = null;
        $this->querySelect = ['*'];

        return $this;
    }

    /**
     * Deger dizisini hazirlanmis PDO ifadesine baglar.
     *
     * @param PDOStatement $stmt Hazirlanmis ifade.
     * @param array<string, mixed> $params Isimli parametreler.
     * @return void
     */
    protected static function bindParams(PDOStatement $stmt, array $params): void
    {
        foreach ($params as $key => $value) {
            $stmt->bindValue((string) $key, $value, self::getPdoType($value));
        }
    }

    /**
     * Bir deger icin uygun PDO parametre tipini belirler.
     *
     * @param mixed $value Baglanacak deger.
     * @return int PDO parametre tipi sabiti.
     */
    protected static function getPdoType(mixed $value): int
    {
        return match (true) {
            $value === null => PDO::PARAM_NULL,
            is_int($value) => PDO::PARAM_INT,
            is_bool($value) => PDO::PARAM_BOOL,
            default => PDO::PARAM_STR,
        };
    }

    /**
     * Aktif surucuyu kullanarak bir tablonun sutun listesini okur.
     *
     * @param string $table Tablo adi.
     * @return array<int, array{name: string, type: string}> Sutun metadatasi.
     */
    protected static function describeTable(string $table): array
    {
        $db = self::db();
        $driver = (string) $db->getAttribute(PDO::ATTR_DRIVER_NAME);

        return match ($driver) {
            'sqlite' => self::describeSqliteTable($table),
            'mysql' => self::describeMysqlTable($table),
            'pgsql' => self::describePgsqlTable($table),
            default => throw new RuntimeException(sprintf('Unsupported database driver for model generation: %s', $driver)),
        };
    }

    /**
     * Cache ayari uygunsa tablo sutunlarini dosyadan okumaya calisir.
     *
     * @param string $table Tablo adi.
     * @return array<int, array{name: string, type: string}>|null
     */
    protected static function loadColumnsFromCache(string $table): ?array
    {
        if (! self::isModelCacheEnabled() || self::shouldRefreshModelCache()) {
            return null;
        }

        $file = self::modelCacheFile($table);

        if ($file === '' || ! is_file($file)) {
            return null;
        }

        $payload = json_decode((string) file_get_contents($file), true);

        if (! is_array($payload) || ! isset($payload['columns']) || ! is_array($payload['columns'])) {
            return null;
        }

        $columns = [];

        foreach ($payload['columns'] as $column) {
            if (! is_array($column) || ! isset($column['name'])) {
                continue;
            }

            $columns[] = [
                'name' => (string) $column['name'],
                'type' => (string) ($column['type'] ?? 'text'),
            ];
        }

        return $columns === [] ? null : $columns;
    }

    /**
     * Cache ayari uygunsa tablo sutunlarini dosyaya yazar.
     *
     * @param string $table Tablo adi.
     * @param array<int, array{name: string, type: string}> $columns Sutun listesi.
     * @return void
     */
    protected static function writeColumnsToCache(string $table, array $columns): void
    {
        if (! self::isModelCacheEnabled()) {
            return;
        }

        $file = self::modelCacheFile($table);

        if ($file === '') {
            return;
        }

        $directory = dirname($file);

        if (! is_dir($directory) && ! @mkdir($directory, 0777, true) && ! is_dir($directory)) {
            return;
        }

        $payload = [
            'driver' => self::$driver,
            'schema' => self::$schema,
            'table' => $table,
            'generated_at' => time(),
            'columns' => array_values($columns),
        ];

        @file_put_contents($file, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Model metadata cache mekanizmasinin aktif olup olmadigini belirtir.
     *
     * @return bool
     */
    protected static function isModelCacheEnabled(): bool
    {
        return (bool) (self::$modelCacheConfig['enabled'] ?? false)
            && (string) (self::$modelCacheConfig['path'] ?? '') !== '';
    }

    /**
     * Bu istekte cache yeniden uretilmeli mi bilgisini dondurur.
     *
     * @return bool
     */
    protected static function shouldRefreshModelCache(): bool
    {
        return (bool) (self::$modelCacheConfig['refresh'] ?? false);
    }

    /**
     * Verilen tablo icin metadata cache dosya yolunu uretir.
     *
     * @param string $table Tablo adi.
     * @return string
     */
    protected static function modelCacheFile(string $table): string
    {
        $path = rtrim((string) (self::$modelCacheConfig['path'] ?? ''), '/');

        if ($path === '') {
            return '';
        }

        $namespace = preg_replace('/[^a-zA-Z0-9_\-]+/', '-', (string) (self::$modelCacheConfig['namespace'] ?? 'default'));
        $fileName = strtolower(self::$driver . '-' . self::$schema . '-' . $table . '.json');

        return $path . '/' . trim((string) $namespace, '-') . '/' . $fileName;
    }

    /**
     * Verilen tablo icin SQLite sutun metadatasini okur.
     *
     * @param string $table Tablo adi.
     * @return array<int, array{name: string, type: string}> Sutun metadatasi.
     */
    protected static function describeSqliteTable(string $table): array
    {
        $stmt = self::db()->query(sprintf('PRAGMA table_info(%s)', self::quoteIdentifier($table)));
        $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        if ($rows === []) {
            throw new RuntimeException(sprintf('Table not found for model generation: %s', $table));
        }

        return array_map(static fn (array $row): array => [
            'name' => $row['name'],
            'type' => $row['type'] ?? 'TEXT',
        ], $rows);
    }

    /**
     * Verilen tablo icin MySQL sutun metadatasini okur.
     *
     * @param string $table Tablo adi.
     * @return array<int, array{name: string, type: string}> Sutun metadatasi.
     */
    protected static function describeMysqlTable(string $table): array
    {
        $stmt = self::db()->query(sprintf('DESCRIBE %s', self::quoteIdentifier($table)));
        $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        if ($rows === []) {
            throw new RuntimeException(sprintf('Table not found for model generation: %s', $table));
        }

        return array_map(static fn (array $row): array => [
            'name' => $row['Field'],
            'type' => $row['Type'] ?? 'varchar',
        ], $rows);
    }

    /**
     * Verilen tablo icin PostgreSQL sutun metadatasini okur.
     *
     * @param string $table Tablo adi.
     * @return array<int, array{name: string, type: string}> Sutun metadatasi.
     */
    protected static function describePgsqlTable(string $table): array
    {
        $sql = 'SELECT column_name, data_type FROM information_schema.columns WHERE table_schema = :schema AND table_name = :table ORDER BY ordinal_position';
        $stmt = self::db()->prepare($sql);

        if (! $stmt instanceof PDOStatement) {
            throw new RuntimeException('Failed to prepare PostgreSQL schema query.');
        }

        $stmt->bindValue(':schema', self::$schema, PDO::PARAM_STR);
        $stmt->bindValue(':table', $table, PDO::PARAM_STR);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($rows === []) {
            throw new RuntimeException(sprintf('Table not found for model generation: %s', $table));
        }

        return array_map(static fn (array $row): array => [
            'name' => $row['column_name'],
            'type' => $row['data_type'] ?? 'text',
        ], $rows);
    }

    /**
     * Bir tanimlayicinin yalnizca guvenli tablo/sutun karakterleri icerdigini dogrular.
     *
     * @param string $identifier Dogrulanacak tanimlayici.
     * @return string Temizlenmis tanimlayici.
     */
    protected static function sanitizeIdentifier(string $identifier): string
    {
        if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $identifier)) {
            throw new RuntimeException(sprintf('Invalid identifier: %s', $identifier));
        }

        return $identifier;
    }

    /**
     * Aktif SQL surucusu icin bir tanimlayiciyi tirnaklar.
     *
     * @param string $identifier Tirnaklanacak tanimlayici.
     * @return string Tirnaklanmis tanimlayici.
     */
    protected static function quoteIdentifier(string $identifier): string
    {
        $clean = self::sanitizeIdentifier($identifier);

        return match (self::$driver) {
            'mysql' => '`' . $clean . '`',
            default => '"' . $clean . '"',
        };
    }

    /**
     * Surucuye ozel LIMIT bolumu sablonunu dondurur.
     *
     * @return string LIMIT bolumu sablonu.
     */
    protected static function limitClause(): string
    {
        return match (self::$driver) {
            'pgsql' => 'LIMIT :limitps OFFSET :limitoffset',
            default => 'LIMIT :limitoffset, :limitps',
        };
    }
}

\class_alias(Models::class, 'models');

/**
 * Tablo metadatasindan uretilen genel amacli dinamik model nesnesi.
 */
#[AllowDynamicProperties]
final class GenericModel extends Models
{
}
