<?php
namespace Parser;

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
    public function getCompanies($query = ''){

        $query = (string)$query;
        $page = 1;
        $contents = array();
        while(true){
            $jsonContent = file_get_contents("http://catalog.api.2gis.ru/2.0/catalog/branch/search?page={$page}&page_size=50&q={$query}&stat%5Bpr%5D=1&region_id=1&fields=dym%2Chash%2Crequest_type%2Citems.adm_div%2Citems.contact_groups%2Citems.flags%2Citems.address%2Citems.rubrics%2Citems.name_ex%2Citems.point%2Citems.external_content%2Citems.org%2Citems.reg_bc_url%2Citems.schedule%2Ccontext_rubrics%2Cwidgets%2Cfilters%2Citems.reviews&key=rudcgu3317");
            $content = json_decode($jsonContent);
            $count = 0;
            if(isset($content->result) && isset($content->result->items)){
                $count = count($content->result->items);
            }
            if($count > 0){
                $contents = array_merge($contents, $content->result->items);
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
    public function getData($query){

        $query = urlencode($query);
        $companies = $this->getCompanies($query);
        $contacts = array();

        foreach($companies as $k => $company){

            $contact = array(
                'name' => $company->name,
                'adress' => isset($company->address_name) ? $company->address_name : '',
                'contacts' => array(),
            );

            $iterator = new \RecursiveArrayIterator($company->contact_groups);
            foreach(new \RecursiveIteratorIterator($iterator) as $key => $value) {
                if($key === 'text'){
                    $contact['contacts'][] = $value;
                }
            }
            $contacts[] = $contact;
        }

        return $contacts;
    }
}
