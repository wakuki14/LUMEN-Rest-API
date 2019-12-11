<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class MultiLang extends Model
{
    const SOURCE_DATA = 1;
    const SOURCE_SCRIPT = 2;
    const SOURCE_PLUGIN = 3;
    
    protected $table = 'multi_langs';
    
    /**
     * 
     * Save multiple lang
     * 
     * @param array $data
     * @param int $foreignId
     * @param string $model
     * @param int $source
     */
    public function saveMultiLang($data, $foreignId, $model, $source = self::SOURCE_DATA)
    {
        foreach ($data as $locale => $locale_arr)
        {
            foreach ($locale_arr as $field => $content)
            {
                $this->insertGetId([
                    'foreign_id' => $foreignId,
                    'model' => $model,
                    'locale' => $locale,
                    'field' => $field,
                    'content' => $content,
                    'source' => $source
                ]);
            }
        }
    }
    
    /**
     * 
     * Udpate multiple languages
     * 
     * @param array $data
     * @param int $foreignId
     * @param string $model
     * @param int $source
     * @return array
     */
    public function updateMultiLang($data, $foreign_id, $model, $source = self::SOURCE_DATA)
    {
        foreach ($data as $locale => $locale_arr)
        {
            foreach ($locale_arr as $field => $content)
            {
                if(!is_array($content))
                {
                    $record = $this->where('foreign_id', $foreign_id)
                    ->where('model', $model)
                    ->where('locale', $locale)
                    ->where('field', $field)
                    ->first();
                    if ($record) {
                        $record->content = $content;
                        $record->save();
                    } else {
                        $this->insertGetId([
                            'foreign_id' => $foreign_id,
                            'model' => $model,
                            'locale' => $locale,
                            'field' => $field,
                            'content' => $content,
                            'source' => $source
                        ]);
                    }

                }
            }
        }
       
    }
    
    /**
     * Get multiple languages
     * 
     * @param int $foreignId
     * @param string $model
     * @return array
     */
    public function getMultiLang($foreignId, $model)
    {
        $arr = [];
        $_arr = $this->where('foreign_id', $foreignId)->where('model', $model)->get()->toArray();
        foreach ($_arr as $_v)
        {
            $arr[$_v['locale']][$_v['field']] = $_v['content'];
        }
        return $arr;
    }
    
    /**
     * 
     * Get multiple record
     * 
     * @param int $foreignId
     * @param string $model
     * @param int $localeId
     * @return Model
     */
    public function getMultiLangRecord($foreignId, $model, $localeId)
    {
        return $this->where('foreign_id', $foreignId)->where('model', $model)->where('locale', $localeId)->first();
    }
}