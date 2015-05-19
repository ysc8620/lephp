<?php
/**
 *
 */
class core_sphinx{
    public  $cl = null;
    static private $conn = null;
    private $match_mode = SPH_MATCH_ANY;
    private $ranking_mode = SPH_RANK_SPH04;
    private $offset = 0;
    private $limit = 20;
    private $index = '*';
    private $keyword = '';
    private $_maxmatches = 50000;

    function __construct(){
        $this->cl = new core_sphinx_sphinx ();
        $this->cl->SetServer(core_config::get('sphinx.hostname'), core_config::get('sphinx.hostport'));

        $this->cl->SetConnectTimeout(6);
        $this->cl->SetArrayResult(true);
    }

    static function init(){
        if(is_null(self::$conn)){
             self::$conn = new core_sphinx();
        }

        return self::$conn;
    }

    public  function create_conf(){
    }

    public function create_index( $index="*" ){

    }

    public function update_index( $index="*" ){

    }

    public function restart_searchd(){

    }

    private function set_params($args){
        // 重置搜索条件
        $this->cl->ResetFilters();

        if(isset($args['limit'])){
            $this->limit = (int)$args['limit'];
        }

        if(isset($args['page']) && $args['page']>0){
            $this->offset = ($args['page'] - 1) * $this->limit;
        }

        if(isset($args['filter'])){
            foreach($args['filter'] as $filter){
                $this->cl->SetFilter($filter[0], array($filter[1]));
            }
        }

        if(isset($args['filter_range'])){
            foreach($args['filter_range'] as $filter){
               $this->cl->SetFilterRange($filter[0], $filter[1],$filter[2],(isset($filter[3])?$filter[3]:false));
            }
        }

//        if($args['sort']){
//            $this->cl->SetSortMode(SPH_SORT_EXTENDED, $args['sort']);
//        }

        if($args['index']){
            $this->index = $args['index'];
        }

        if($args['keyword']){
            $this->keyword = $args['keyword'];
        }

        if($args['match_mode']){
            $this->match_mode = $args['match_mode'];
        }

        if($args['ranking_mode']){
            $this->ranking_mode = $args['ranking_mode'];
        }

        $this->cl->_maxmatches = $this->_maxmatches;
        $this->cl->SetLimits($this->offset, $this->limit);

        $this->cl->SetMatchMode($this->match_mode);
        //$this->cl->SetWeights ( array ( 100, 1 ) );
        $this->cl->SetRankingMode($this->ranking_mode);
    }

    static public function map_keywords_id($id){
        return $id['id'];
    }
    /**
     * @param array $args
     *  page => 查询页 (可选 默认1)
     * sort => 排序  price DESC, add_time DESC （可选）
     * filter => 检索条件 二维数组 array(  array('filed', value),  array('filed', value)) （可选）
     * filter_range => 检索范围 二维数组   array(  array(filed, from, to, bool(是否包含from/to)默认false )) （可选）
     * offset => 每页数量 （可选 默认20）
     * index  => 要搜索的索引  （可选 默认全部*）
     * keyword => 需要查询的关键字 (可选)
     * match_mode => 匹配模式
     *
     * example
     * sphinx::init()->search(

     *      array(
                'page'=>1
     *          'sort' =>'game_add_time DESC',
     *          'filter' => array(  array('game_state', 1) ),
     *          'filter_range' => array(   array('game_add_time', strtotime("2014-08-10"), time())  ),
     *          'offset' => 10,
     *          'index' => 'game_info',
     *          'keyword' => '跑酷'
     *      )
     * )
     * @return array
     */
    public function test($args = array()){
        $this->set_params($args);
        $data = $this->cl->Query($this->keyword, $this->index);

        $return  = array();
        $return['list'] = array();
        if($data['matches']){
            foreach($data['matches'] as $item ){
                $return['list'][] = $item['id'];
            }
        }
        $return['words'] = $data['words'];
        $return['total'] = $data['total_found'];
        return $return;
    }

    // 记录时间 ip 关键字
    public function add_search_log($keywords){
        // 创建日志目录
        if(!file_exists('/data/sphinx/')){
            mkdir(('/data/sphinx/'),0755,true);
        }
        // 替换词
        $keywords = str_replace(',',' ',$keywords);

        tools_file::to_log('/data/sphinx/search.log', date("Y-m-d H:i:s").','.core_comm::get_ip().','.$keywords);
    }

    public function Status(){
        return $this->cl->Status();
    }

    public function buildKeywords($keywords){
        $data = $this->cl->BuildKeywords ( $keywords, "keywords_index", false );
      //  $data = $this->cl->Query($keyword, $index);
        return $data;
    }
    /**
     * 获取分词
     *
     * @param $keyword
     * @return mixed
     */
    public function get_keywords_ids($keyword, $index='keywords_index'){

        //set_time_limit(0);
        $this->cl->SetMatchMode(SPH_MATCH_ANY);
        //$this->cl->SetSortMode(SPH_SORT_RELEVANCE);
//        $this->cl->SetRankingMode(SPH_RANK_BM25);
//        $this->cl->SetSortMode ( SPH_SORT_RELEVANCE );
        $this->cl->SetLimits( 0, 5);
        $data = $this->cl->Query($keyword, $index);

//        $this->cl->SetRankingMode(SPH_RANK_BM25);
//        $this->cl->SetSortMode ( SPH_SORT_RELEVANCE );
//        $this->cl->AddQuery ($keyword,$index);
//        $this->cl->SetSortMode ( SPH_SORT_RELEVANCE, "published desc" );
//        $this->cl->AddQuery ( $keyword, $index );
//        $this->cl->SetSortMode ( SPH_SORT_RELEVANCE, "published asc" );
//        $this->cl->AddQuery ( $keyword, $index );
//        $res = $this->cl->RunQueries();
//        print_r($res);
        if(tools_filter::r('test',F_INT) == 1){
            echo '<pre>
            ====关键字部分=========================================';
            //print_r($this->cl);
            //print_r($data);
            print_r($data['matches']);
            print_r($data['words']);
            echo '
            ====关键字部分=========================================
            </pre>';
        }
        $words = array();
        if($data['words']){
            foreach($data['words'] as $key=>$hits){
                $words[] = $key;
            }
        }
        $tags = array();
        if($data['matches']){
            foreach($data['matches'] as $v){
                $tags[] = array('id'=>$v['id'], 'keyword'=>$v['attrs']['keywords']);
            }
        }
        return array('data'=>$tags, 'words'=>$words);
    }

    /**
     * @param array $args
     *  page => 查询页 (可选 默认1)
     * sort => 排序  price DESC, add_time DESC （可选）
     * filter => 检索条件 二维数组 array(  array('filed', value),  array('filed', value)) （可选）
     * filter_range => 检索范围 二维数组   array(  array(filed, from, to, bool(是否包含from/to)默认false )) （可选）
     * offset => 每页数量 （可选 默认20）
     * index  => 要搜索的索引  （可选 默认全部*）
     * kw => 需要查询的关键字 (可选)
     * match_mode => 匹配模式
     *
     * example
     * sphinx::init()->search(

     *      array(
                'page'=>1
     *          'offset' => 10,
     *          'kw' => '跑酷',
     *          'ext'=>'123:211,123:333',
     *          'cat'=>'123,122,'
     *      )
     * )
     * @return array
     */
    public function query($args = array()){
        $query = '';
        // 获取关键字 Keyword id
        if($args['kw']){
            //$this->add_search_log($args['kw']);
            $data = $this->get_keywords_ids($args['kw']);
            //print_r($data['data']);
            if($data['data']){
                $key_ids = core_weight::weight($args['kw'], $data['data'], $data['words']);
                if($key_ids){
                    $kids = array_map("core_sphinx::map_keywords_id", $key_ids);
                    if($key_ids){
                        $query = '@keywords_ids('.join(' | ',$kids).') ';
                    }
                }
            }else{
                $query = '-';
            }
        }

        // 重置搜索条件
        $this->cl->ResetFilters();

        if(isset($args['limit'])){
            $this->limit = (int)$args['limit'];
        }

        if(isset($args['page'])){
            $this->offset = ($args['page'] - 1) * $this->limit;
        }

        if(isset($args['filter'])){
            foreach($args['filter'] as $filter){
                $this->cl->SetFilter($filter[0], is_array($filter[1])?$filter[1]:array($filter[1]));
            }
        }

        if(isset($args['filter_range'])){
            foreach($args['filter_range'] as $filter){
                $this->cl->SetFilterRange($filter[0], $filter[1],$filter[2],(isset($filter[3])?$filter[3]:false));
            }
        }

        $this->cl->SetMatchMode(SPH_MATCH_EXTENDED2);
        //$query = ' ';
        if($args['page']){
            $this->cl->SetLimits( $this->limit * ($args['page']-1), $this->limit);
        }
        if($args['query']){
            $query = $args['query'];
        }

        if($args['cat']){
            $query = $query .' @cats_ids('.$args['cat'].')';
        }

        if($args['ext']){
            $query = $query .' @attrs_ids('.$args['ext'].')';
        }

        if($args['sort']){
            $this->cl->SetSortMode(SPH_SORT_EXTENDED, $args['sort']);
        }

        $query = trim( trim($query), '|');
       //var_dump($query);

        $data = $this->cl->Query($query, 'info_index');

        if(tools_filter::r('test',F_INT) == 1){
            echo '<pre>
            ====搜索部分=========================================';
            print_r($this->cl);
            //print_r($data['matches']);
            print_r($data);
            echo '
            ====搜索部分=========================================
            </pre>';
        }
        $return  = array();
        $return['list'] = array();
        if($data['matches']){
            foreach($data['matches'] as $item ){
                $return['list'][] = $item['attrs']['goods_id'];
            }
        }
        $return['words'] = $data['words'];
        $return['total'] = $data['total_found'];
        return $return;
    }

    public function search($args = array()){
        $keywords_query = '';
        $query = '';
        // 获取关键字 Keyword id
        if($args['kw']){
            $this->add_search_log($args['kw']);
            $data = $this->get_keywords_ids($args['kw']);
            //print_r($data['data']);
            if($data['data']){
                $key_ids = core_weight::weight($args['kw'], $data['data'], $data['words']);
                if($key_ids){
                    $kids = array_map("core_sphinx::map_keywords_id", $key_ids);
                    if($key_ids){
                        $keywords_query = '@keywords_ids('.join(' | ',$kids).') ';
                    }
                }
            }

        }

        // 重置搜索条件
        $this->cl->ResetFilters();

        if($keywords_query){
            $query = "($keywords_query | @title({$args['kw']}))";
        }else{
            if($args['kw']){
                $query = "@title({$args['kw']})";
            }
        }

        if(isset($args['limit'])){
            $this->limit = (int)$args['limit'];
        }
        # 分页数据
        if(isset($args['page'])){
            $this->cl->SetLimits( $this->limit * ($args['page']-1), $this->limit);
        }

        if(isset($args['filter'])){
            foreach($args['filter'] as $filter){
                $this->cl->SetFilter($filter[0], is_array($filter[1])?$filter[1]:array($filter[1]));
            }
        }

        if(isset($args['filter_range'])){
            foreach($args['filter_range'] as $filter){
                if(is_float($filter[1])){
                    $this->cl->SetFilterFloatRange($filter[0], $filter[1],$filter[2],(isset($filter[3])?$filter[3]:false));
                }else{
                    $this->cl->SetFilterRange($filter[0], $filter[1],$filter[2],(isset($filter[3])?$filter[3]:false));
                }

            }
        }

        $this->cl->SetMatchMode(SPH_MATCH_EXTENDED2);

        if($args['cat']){
            $query = $query .' @cats_ids('.$args['cat'].')';
        }

        if($args['ext']){
            $args['ext'] = str_replace(':','S',$args['ext']);
            $query = $query .' @attrs_ids('.$args['ext'].')';
        }

        if($args['sort']){
            $this->cl->SetSortMode(SPH_SORT_EXTENDED, $args['sort']);
        }

        $data = $this->cl->Query($query, 'search_index,search_index,goods_index');

        if(tools_filter::r('test',F_INT) == 1){
            echo '<pre>
            ====搜索部分=========================================';
            print_r($query);
            print_r($this->cl);
            //print_r($data['matches']);
            print_r($data);
            echo '
            ====搜索部分=========================================
            </pre>';
        }
        $return  = array();
        $return['list'] = array();
        if($data['matches']){
            foreach($data['matches'] as $item ){
                #print_r($item);
                $return['list'][] = $item['id'];
                $return['data'][] = $item['attrs'];
            }
        }
        $return['words'] = $data['words'];
        $return['total'] = $data['total_found'];
        return $return;
    }


}