<?php

namespace App\Console\Commands;

use App\Models\Reference;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MakeReference extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = 'table1';
        $tableName = Str::start($name, 'reference_');

        $isCreating = ! Schema::hasTable($tableName);
        $command = $isCreating ? 'create' : 'table';

        $columns = Schema::getColumnListing($tableName);

        /** @var Reference $model */
        $model = $this->getModel($name);
        $rows = $model::query()->get();

//        $row = $model::create([
//            'name' => '2'
//        ]);

//        Schema::$command($tableName, function (Blueprint $table) use ($isCreating, $columns) {
//            if ($isCreating) {
//                $table->id();
//            }
//
//            $table->string('name', 256)->nullable()->change();
//
//            if ($isCreating) {
//                $table->timestamps();
//            }
//
//            $columns = $table->getColumns();
//        });

        return Command::SUCCESS;
    }

    private function getModel($name)
    {
        $tableName = Str::start($name, 'reference_');

        return new class($tableName) extends Reference
        {
            public static ?string $referenceTable;

            public function __construct($tableName = null)
            {
                parent::__construct([]);

                $this->setTable($tableName ?? static::$referenceTable);
                static::$referenceTable = $this->getTable();
            }
        };
    }
}
