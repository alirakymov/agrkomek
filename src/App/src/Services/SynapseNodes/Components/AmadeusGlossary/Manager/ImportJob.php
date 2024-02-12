<?php

namespace Qore\App\SynapseNodes\Components\AmadeusGlossary\Manager;

use Qore\QueueManager\JobAbstract;
use Qore\QueueManager\JobInterface;
use Qore\QueueManager\JobTrait;
use Qore\Qore;
use Qore\ORM\ModelManager;

/**
 * Class: ImportJob
 *
 * For start this job use command:
 * ./assistant queue:worker start Qore\\App\\SynapseNodes\\Components\\AmadeusGlossary\\Manager\\ImportJob
 *
 * @see JobInterface
 * @see JobAbstract
 */
class ImportJob extends JobAbstract implements JobInterface
{
    use JobTrait;

    protected static $name = null;
    protected static $persistence = false;
    protected static $acknowledgement = true;
    protected static $workersNumber = 1;

    /**
     * process
     *
     *
     */
    public function process() : bool
    {
        $mm = Qore::service(ModelManager::class);
        $row = 0;
        $firstRowItems = [];
        $glossary = $mm('SM:AmadeusGlossary')->where(function($_where)  {
            $_where(['@this.id' => $this->task['id-glossary']]);
        })->one();

        if (($handle = fopen($this->task['file'], "r")) !== FALSE) {
            while (($data = fgetcsv($handle)) !== FALSE) {//read file by line
                $mm->getEntityProvider()->reset();
                if ($row === 0) {
                    for ($i = 0; $i < count($data);$i++) {
                        $firstRowItems[$i] = $data[$i];
                    }
                    if (array_search($glossary->target,$firstRowItems) === false) {
                        break;
                    }
                    $row++;
                    continue;
                }
                $prepareData = [];
                for ($i = 0; $i < count($firstRowItems);$i++) {
                    $prepareData += [
                        $firstRowItems[$i] => $data[$i]
                    ];
                }

                $code = $prepareData[$glossary->target];
                $item = $mm('SM:AmadeusGlossaryItem')->where(function($_where) use ($code) {//where code == bla bla && id gloss == bal bla
                    $_where([
                        '@this.code' => $code,
                        '@this.idGlossary' => $this->task['id-glossary']]
                    );
                })->one();

                if(is_null($item)) {
                    $item = $mm ('SM:AmadeusGlossaryItem', [
                        'code' => $code,
                        'data' => $prepareData
                    ]);
                } else {
                    $item['data'] = $prepareData;
                }

                $item->link('glossary',$glossary);
                $mm ($item)->save();
            }
            fclose($handle);
            unlink($this->task['file']);
        }
        return true;
    }

}
