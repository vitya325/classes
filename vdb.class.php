<?php
class VDB {
    protected $dbHost;
    protected $dbUser;
    protected $dbPassword;
    protected $dbName;
    protected $table;
    protected $type;
    protected $params = array();    
    protected $conn;
    public $result;
        
    
        function __construct($host, $user, $password, $name, $persistent_connection = false) {
            error_reporting(E_ALL | E_STRICT);
            
            if(empty($host) || empty($user) || empty($password) || empty($name)){
                $this->showError('Connection parameters were not set properly');
            }
            $this->dbHost = $host;
            $this->dbUser = $user;
            $this->dbPassword = $password;
            $this->dbName = $name;
            //$this->openConnection();            
        }
        function __destruct(){            
        }
        function __clone() {
            $this->showError("Object cannot be cloned.");
        }
        public function showError($error){
            echo '<p> ===== ERROR ===== </p>',
                 '<p>'.$error.'</p>';   
        }
        public function openConnection(){
            $conn = mysqli_connect($this->dbHost, $this->dbUser, $this->dbPassword, $this->dbName);
            if(mysqli_connect_errno()){
                $this->showError(mysqli_connect_error());
            } else {
                $this->conn = $conn;
            }
        }                
        public function select($fields = ''){
            $this->type = 'SELECT';
            $this->params['fields'] = $fields;
            return $this;
        }
        public function insert(){
            $this->type = 'INSERT';
            return $this;
        }
        public function update(){
            $this->type = 'UPDATE';
            return $this;
        }
        public function delete(){
            $this->type = 'DELETE';
            return $this;
        }
        public function table($table){
            $this->table = $table;
            return $this;
        }
        public function where($field, $operator = '=', $value){
            $this->params['where']['field'] = $field;
            $this->params['where']['operator'] = $operator;
            $this->params['where']['value'] = $value;
            return $this;
        }
        public function execute($result_type = null){
            if(!is_null($result_type)){
                $this->params['result_type'] = $result_type;
            }
            //$this->result = $this->prepareQuery($this->type, $this->table, $this->params);
            return $this->prepareQuery($this->type, $this->table, $this->params);
        }
        protected function prepareQuery($type, $table, $params){
            $sql_query = "";
            switch(strtoupper($type)){
                case 'SELECT':                    
                    if(!empty($params['fields'])){
                        $alias = substr($table, 0, 2);
                        $affect_fields = explode(',', $params['fields']);
                        $count_fields = sizeof($affect_fields)-1;
                        $sql_query = "$type ";
                        
                        foreach($affect_fields as $key => $field){
                            if($key != $count_fields){
                                $sql_query .= $alias."`$field`,";
                            } else {
                                $sql_query .= $alias."`$field`";
                            }                            
                        }
                        $sql_query .= " FROM $table $alias";
                        
                        if(isset($params['where'])){
                            $sql_query .= " WHERE";
                            if($params['where']['operator'] === '!='){
                                $params['where']['operator'] = '<>';
                            }
                            $sql_query .= " $alias.`{$params['where']['field']}` {$params['where']['operator']} $alias.`{$params['where']['value']}`";
                        }
                    } else {
                        $sql_query = "$type * FROM $table";
                            if(isset($params['where'])){
                            $sql_query .= " WHERE";
                            if($params['where']['operator'] === '!='){
                                $params['where']['operator'] = '<>';
                            }
                            $sql_query .= " {$params['where']['field']} {$params['where']['operator']} {$params['where']['value']}";
                        }
                    }                     
                    return $sql_query;
                    break;
                case 'INSERT':
                    $sql_query .= $type;
                    break;
                case 'UPDATE':
                    $sql_query .= $type;
                    break;
                case 'DELETE':
                    $sql_query .= $type;
                    break;
                default:
                    break;
            }
        }
        protected function executeQuery($sql){
            $this->openConnection();
            
            $result_db_object = mysqli_query($this->conn, $sql);
            return $result_db_object;
        }
}
?>
