<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables whose primary key should become a UUID.
     *
     * @var list<string>
     */
    private array $tables = [
        'users',
        'warehouses',
        'locations',
        'products',
        'suppliers',
        'couriers',
        'purchase_orders',
        'purchase_order_items',
        'receipts',
        'receipt_items',
        'inventory_stocks',
        'stock_movements',
        'stock_transfers',
        'sales_orders',
        'sales_order_items',
        'stock_allocations',
        'shipments',
        'stock_opnames',
        'stock_opname_items',
        'stock_adjustments',
        'return_orders',
        'return_items',
        'audit_logs',
    ];

    /**
     * Polymorphic audit targets and the table that owns each target.
     *
     * @var array<class-string, string>
     */
    private array $morphTables = [
        'App\Models\AuditLog' => 'audit_logs',
        'App\Models\Courier' => 'couriers',
        'App\Models\InventoryStock' => 'inventory_stocks',
        'App\Models\Location' => 'locations',
        'App\Models\Product' => 'products',
        'App\Models\PurchaseOrder' => 'purchase_orders',
        'App\Models\PurchaseOrderItem' => 'purchase_order_items',
        'App\Models\Receipt' => 'receipts',
        'App\Models\ReceiptItem' => 'receipt_items',
        'App\Models\ReturnItem' => 'return_items',
        'App\Models\ReturnOrder' => 'return_orders',
        'App\Models\SalesOrder' => 'sales_orders',
        'App\Models\SalesOrderItem' => 'sales_order_items',
        'App\Models\Shipment' => 'shipments',
        'App\Models\StockAdjustment' => 'stock_adjustments',
        'App\Models\StockAllocation' => 'stock_allocations',
        'App\Models\StockMovement' => 'stock_movements',
        'App\Models\StockOpname' => 'stock_opnames',
        'App\Models\StockOpnameItem' => 'stock_opname_items',
        'App\Models\StockTransfer' => 'stock_transfers',
        'App\Models\Supplier' => 'suppliers',
        'App\Models\User' => 'users',
        'App\Models\Warehouse' => 'warehouses',
    ];

    /**
     * Convert existing integer primary and foreign keys to UUIDs without losing data.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql' || ! $this->hasLegacyIntegerKeys()) {
            return;
        }

        $foreignKeys = $this->foreignKeys();
        $hasSessionUserId = Schema::hasTable('sessions') && Schema::hasColumn('sessions', 'user_id');
        $hasAuditableId = Schema::hasTable('audit_logs') && Schema::hasColumn('audit_logs', 'auditable_id');

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach ($foreignKeys as $foreignKey) {
                DB::statement(sprintf(
                    'ALTER TABLE `%s` DROP FOREIGN KEY `%s`',
                    $foreignKey['table'],
                    $foreignKey['constraint'],
                ));
            }

            foreach ($this->tables as $table) {
                DB::statement("ALTER TABLE `{$table}` ADD COLUMN `old_id` BIGINT UNSIGNED NULL");
                DB::statement("UPDATE `{$table}` SET `old_id` = `id`");
                DB::statement("ALTER TABLE `{$table}` MODIFY `id` CHAR(36) NOT NULL");
                DB::statement("UPDATE `{$table}` SET `id` = LOWER(UUID())");
            }

            foreach ($foreignKeys as $foreignKey) {
                $nullable = $foreignKey['nullable'] ? 'NULL' : 'NOT NULL';

                DB::statement("ALTER TABLE `{$foreignKey['table']}` MODIFY `{$foreignKey['column']}` CHAR(36) {$nullable}");
                DB::statement(sprintf(
                    'UPDATE `%s` child JOIN `%s` parent ON child.`%s` = CAST(parent.`old_id` AS CHAR) SET child.`%s` = parent.`id` WHERE child.`%s` IS NOT NULL',
                    $foreignKey['table'],
                    $foreignKey['referenced_table'],
                    $foreignKey['column'],
                    $foreignKey['column'],
                    $foreignKey['column'],
                ));
            }

            if ($hasSessionUserId) {
                DB::statement('ALTER TABLE `sessions` MODIFY `user_id` CHAR(36) NULL');
                DB::statement('UPDATE `sessions` child JOIN `users` parent ON child.`user_id` = CAST(parent.`old_id` AS CHAR) SET child.`user_id` = parent.`id` WHERE child.`user_id` IS NOT NULL');
            }

            if ($hasAuditableId) {
                DB::statement('ALTER TABLE `audit_logs` MODIFY `auditable_id` CHAR(36) NULL');

                foreach ($this->morphTables as $class => $table) {
                    DB::statement(sprintf(
                        'UPDATE `audit_logs` child JOIN `%s` parent ON child.`auditable_id` = CAST(parent.`old_id` AS CHAR) SET child.`auditable_id` = parent.`id` WHERE child.`auditable_type` = ? AND child.`auditable_id` IS NOT NULL',
                        $table,
                    ), [$class]);
                }
            }

            foreach ($this->tables as $table) {
                DB::statement("ALTER TABLE `{$table}` DROP COLUMN `old_id`");
            }

            foreach ($foreignKeys as $foreignKey) {
                DB::statement(sprintf(
                    'ALTER TABLE `%s` ADD CONSTRAINT `%s` FOREIGN KEY (`%s`) REFERENCES `%s` (`%s`) ON DELETE %s ON UPDATE %s',
                    $foreignKey['table'],
                    $foreignKey['constraint'],
                    $foreignKey['column'],
                    $foreignKey['referenced_table'],
                    $foreignKey['referenced_column'],
                    $foreignKey['delete_rule'],
                    $foreignKey['update_rule'],
                ));
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting UUID keys to auto-incrementing integers would be destructive.
    }

    private function hasLegacyIntegerKeys(): bool
    {
        $type = DB::table('information_schema.COLUMNS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'users')
            ->where('COLUMN_NAME', 'id')
            ->value('DATA_TYPE');

        return $type === 'bigint';
    }

    /**
     * @return list<array{
     *     table: string,
     *     column: string,
     *     constraint: string,
     *     referenced_table: string,
     *     referenced_column: string,
     *     delete_rule: string,
     *     update_rule: string,
     *     nullable: bool
     * }>
     */
    private function foreignKeys(): array
    {
        $rows = DB::select(
            'SELECT kcu.TABLE_NAME AS `table`, kcu.COLUMN_NAME AS `column`, kcu.CONSTRAINT_NAME AS `constraint`,
                    kcu.REFERENCED_TABLE_NAME AS `referenced_table`, kcu.REFERENCED_COLUMN_NAME AS `referenced_column`,
                    rc.DELETE_RULE AS `delete_rule`, rc.UPDATE_RULE AS `update_rule`, c.IS_NULLABLE AS `nullable`
             FROM information_schema.KEY_COLUMN_USAGE kcu
             JOIN information_schema.REFERENTIAL_CONSTRAINTS rc
               ON rc.CONSTRAINT_SCHEMA = kcu.CONSTRAINT_SCHEMA
              AND rc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
              AND rc.TABLE_NAME = kcu.TABLE_NAME
             JOIN information_schema.COLUMNS c
               ON c.TABLE_SCHEMA = kcu.TABLE_SCHEMA
              AND c.TABLE_NAME = kcu.TABLE_NAME
              AND c.COLUMN_NAME = kcu.COLUMN_NAME
             WHERE kcu.TABLE_SCHEMA = DATABASE()
               AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
             ORDER BY kcu.TABLE_NAME, kcu.CONSTRAINT_NAME'
        );

        return array_map(fn (object $row): array => [
            'table' => $row->table,
            'column' => $row->column,
            'constraint' => $row->constraint,
            'referenced_table' => $row->referenced_table,
            'referenced_column' => $row->referenced_column,
            'delete_rule' => $row->delete_rule,
            'update_rule' => $row->update_rule,
            'nullable' => $row->nullable === 'YES',
        ], $rows);
    }
};
