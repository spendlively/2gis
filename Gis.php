<?php
namespace Gis;

/**
 * Class Parser
 * @package Gis
 *
 * Пример:
 * $query = "Ювелирные украшения";
 * $parser = new \Parser\Gis();
 * $contacts = $parser->getContacts($query);
 */
class Gis implements iParser{

    /**
     * Возвращает массив компаний из 2gis.ru по заросу
     *
     * @param string $query
     * @return array
     */
    public function _getData($query = ''){

        $query = (string)$query;
        $page = 1;
        $contents = array();
        while(true){
            $jsonContent = file_get_contents("http://catalog.api.2gis.ru/2.0/catalog/branch/search?page={$page}&page_size=50&q={$query}&stat%5Bpr%5D=1&region_id=1&fields=dym%2Chash%2Crequest_type%2Citems.adm_div%2Citems.contact_groups%2Citems.flags%2Citems.address%2Citems.rubrics%2Citems.name_ex%2Citems.point%2Citems.external_content%2Citems.org%2Citems.reg_bc_url%2Citems.schedule%2Ccontext_rubrics%2Cwidgets%2Cfilters%2Citems.reviews&key=rudcgu3317");
            $content = json_decode($jsonContent, true);
            $count = 0;
            if(isset($content['result']) && isset($content['result']['items'])){
                $count = count($content['result']['items']);
            }
            if($count > 0){
                $contents = array_merge($contents, $content['result']['items']);
            }
            else{
                break;
            }
            $page++;
        }

        return $contents;
    }

    /**
     * Возвращает массив контактов из 2gis.ru по заросу
     *
     * @param $query
     * @return array
     */
    public function getData($query = null){

        if(empty($query)){
            return array();
        }

        if(is_string($query)){
            $query = array($query);
        }

        $result = array();
        foreach($query as $q){

            $q = urlencode($q);
            $data = $this->_getData($q);
            $companies = $this->getCompanies($data);
            $result = array_merge($result, $companies);
        }

////file_put_contents('qwe', serialize($data));
//$data = unserialize(file_get_contents('qwe'));

        return $result;
    }

    /**
     * Возвращает массив компаний
     *
     * @param $data
     * @return array
     */
    public function getCompanies($data){

        $companies = array();
        foreach($data as $k => $comp){
            $contacts = array();
            $iterator = new GisRecursiveIterator($comp['contact_groups']);
            $recursiveIteratorIterator = new \RecursiveIteratorIterator($iterator);
            foreach($recursiveIteratorIterator as $key => $value) {
                if(!empty($value) && !empty($value['type'])){
                    $contacts[$value['type']][] = $value;
                }
            }
            $company = array(
                'name' => $comp['name'],
                'adress' => isset($comp['address_name']) ? $comp['address_name'] : '',
                'contacts' => $contacts,
            );
            $companies[] = $company;
        }

        return $companies;
    }
}
