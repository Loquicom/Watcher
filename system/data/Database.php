<?php

/*
 * TODO Ajout moyen de logger les requetes + Renvoyer this ou null sur where() + Ajouter méthode select, having et groupby
 */

require __DIR__ . '/DatabaseTable.php';

class Database {

    public static $sqlQueryRegexp = '/^(?:I|i|C|c|D|d|U|u|S|s)(?:[^;\']|(?:\'[^\']+\'))+;\s*$/m';

    /**
     * Options du pilote BD
     * @var array
     */
    private static $driverOptions = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    );

    /**
     * La dernière instance créée
     * @var Database
     */
    private static $instance = null;

    /**
     * L'instance de pdo
     * @var PDO
     */
    private $pdo = null;

    /**
     * Prefix des tables
     * @var string
     */
    private $prefix = '';

    /**
     * Le fetch mode par defaut
     * array, class
     * @var string
     */
    private $fetch = 'array';

    /**
     * Le where des requetes
     * @var string
     */
    private $where = '';

    /**
     * La requete sql
     * @var string
     */
    private $requete = '';

    /**
     * La requete dans pdo
     * @var PDOStatement
     */
    private $statement = null;

    /**
     * Construit une instance de gestion d'une Base de données en utilisant PDO
     * @param string $dbName - Le nom de la base secondaire (vide pour la principal)
     * @param string prefix - Prefixe des tables
     */
    public function __construct($dbName = '', $prefix = '') {
        if (trim($dbName) == '') {
            $dbName = 'db.sqlite';
        }
        $this->pdo = new PDO('sqlite:' . __DIR__ . '/' . $dbName, null, null, self::$driverOptions);
        //Ajout prefix
        $this->prefix = $prefix;
        self::$instance = $this;
    }

    /**
     * Change le fetch mode par defaut
     * @param string $fetchMode - Le fetchmode (array ou class)
     * @return boolean
     */
    public function set_fetch_mode($fetchMode) {
        $fetchMode = strtolower($fetchMode);
        if (in_array($fetchMode, array('array', 'class'))) {
            $this->fetch = $fetchMode;
            return true;
        }
        return false;
    }

    /**
     * Reinitailise la requete à zero
     * (ne change pas le fetch mode)
     */
    public function reset() {
        $this->where = '';
        $this->requete = '';
        $this->statement = null;
    }

    /**
     * Création de la condition where de la requete
     * Trois façon de l'utiliser
     * Passage d'un tableau avec clef = champ et valeur = valeur recherché ex : where(array('id' => '1'))
     * Passage de la clef et de la valeur en parametre ex : where('id', '1')
     * Passage de la clause where directement (sans le mot clef where) ex : where('id = 1 And email is null')
     * @param mixed $data - Les données
     * @param string $val - La valeur
     * @return boolean
     */
    public function where($data, $val = '') {
        //Si 1er appel à where
        if($this->where == ''){
            $this->where = " Where 1=1";
        }
        //Si on utilise en mode where(champ, valeur)
        if (trim($val) != '' && is_string($data)) {
            $this->where .= " And Upper(" . $data . ") = '" . strtoupper($val) . "'";
            return true;
        }
        //Sinon si on utilise en mode where(array(champ => val, ...))
        else if (is_array($data) && !empty($data)) {
            $first = true;
            foreach ($data as $champ => $valeur) {
                $this->where .= " And Upper(" . $champ . ") = '" . strtoupper($valeur) . "'";
            }
            return true;
        }
        //Sinon si $data est un string c'est une clause where deja ecrite
        else if (is_string($data)) {
            $this->where .= " And " . $data;
            return true;
        }
        return false;
    }

    /**
     * Retourne tous les champs d'une table avec le where actuel
     * @param string $table - Le nom de la table
     * @param boolean $retour - Retourner le resultat (optional)
     * @return mixed
     */
    public function get($table, $retour = true) {
        //Creation de la requete
        $this->requete = "Select * From " . $this->prefix . $table;
        $this->requete .= $this->where;
        if ($this->execute() === false) {
            return false;
        }
        //Si on retourne directement
        if ($retour) {
            return $this->result();
        }
        return true;
    }

    /**
     * Retourne tous les champs d'une table avec le where en parametre
     * @see Database::where()
     * @param string $table - Le nom de la table
     * @param string[]|string $where - Les champs/valeur pour le where | La 
     * clause where ecrite sans le mot clef where
     * @param boolean $retour - Retourner le resultat (optional)
     * @return false|mixed
     */
    public function get_where($table, $where, $retour = true) {
        //Ajout du where
        if ($this->where($where) === false) {
            return false;
        }
        //Création de la requete
        $this->requete = "Select * From " . $this->prefix . $table;
        $this->requete .= $this->where;
        if ($this->execute() === false) {
            return false;
        }
        if ($retour) {
            return $this->result();
        }
        return true;
    }

    /**
     * Insert une ou plusieur ligne dans la base
     * 1 ligne $data = array('champ' => 'val', ...)
     * +1 lignes $data = array(array('champ' => 'val', ...), array(...))
     * @param string $table - Le nom de la table
     * @param mixed $data - Les données à insérer
     * @return false|mixed - False si echec, l'id de la ligne si réussie (sous forme de tableau si plusieur ligne)
     */
    public function insert($table, $data) {
        //Si il y a plusieurs insert à faire
        if (isset($data[0]) && is_array($data[0])) {
            //Tableau avec les resultat pour chaque insert
            $result = array();
            foreach ($data as $tab) {
                $result[] = $this->insert($table, $tab);
            }
            return $result;
        }
        //Sinon si il n'y en a qu'un
        else {
            $champs = '';
            $vals = '';
            foreach ($data as $champ => $val) {
                $champs .= $champ . ",";
                $vals .= "'" . $val . "',";
            }
            $this->requete = 'Insert into ' . $this->prefix . $table . '(' . rtrim($champs, ',') . ') Values (' . rtrim($vals, ',') . ');';
            if ($this->execute() === false) {
                return false;
            }
            $this->reset();
            return $this->pdo->lastInsertId();
        }
    }

    /**
     * Met à jour des champ d'une table
     * 1 ligne $id = array('id' => 'val', ...)
     * +1 lignes $id = array(array('id' => 'val', ...), array(...))
     * @param string $table - Le nom de la table
     * @param mixed $id - Le ou les id de la table
     * @param mixed $data - Les données a modifier array('champ' => 'val', ...)
     * @return boolean|boolean[] true ou false selon la reussite, en tableau si plusieurs update
     */
    public function update($table, $id, $data) {
        //Si il y a plusieurs update a faire
        if (isset($id[0]) && is_array($id[0])) {
            $result = array();
            foreach ($id as $tab) {
                $result[] = $this->update($table, $tab, $data);
            }
            return $result;
        }
        //Sinon si il n'y en a qu'un
        else {
            //Conception du where avec le ou les id
            $where = ' Where';
            foreach ($id as $champ => $val) {
                $where .= " Upper(" . $champ . ") = '" . strtoupper($val) . "' And";
            }
            $where = rtrim($where, 'And');
            //Conception du set
            $set = '';
            foreach ($data as $champ => $val) {
                $set .= $champ . " = '" . $val . "',";
            }
            $set = rtrim($set, ',');
            //Ecriture de la requete
            $this->requete = "Update " . $this->prefix . $table . " Set " . $set . $where;
            $result = $this->execute();
            $this->reset();
            return $result;
        }
    }

    /**
     * Supprime des champ d'une table
     * 1 ligne $id = array('id' => 'val', ...)
     * +1 lignes $id = array(array('id' => 'val', ...), array(...))
     * @param string $table - Le nom de la table
     * @param mixed $id - Le ou les id de la table
     * @return boolean|boolean[] true ou false selon la reussite, en tableau si plusieurs delete
     */
    public function delete($table, $id) {
        //Si il y a plusieurs delete a faire
        if (isset($id[0]) && is_array($id[0])) {
            $result = array();
            foreach ($id as $tab) {
                $result[] = $this->delete($table, $tab);
            }
            return $result;
        }
        //Sinon si il n'y en a qu'un
        else {
            //Conception du where avec le ou les id
            $where = ' Where';
            foreach ($id as $champ => $val) {
                $where .= " Upper(" . $champ . ") = '" . strtoupper($val) . "' And";
            }
            $where = rtrim($where, 'And');
            //Ecriture de la requete
            $this->requete = 'Delete From ' . $this->prefix . $table . $where;
            $result = $this->execute();
            $this->reset();
            return $result;
        }
    }

    /**
     * Retourne une ligne sous la forme du fetch mode par defaut
     * @param string $params - Parametre pour le retour
     * @return mixed
     */
    public function row($params = '') {
        if ($this->fetch == 'array') {
            return $this->row_array();
        } else {
            if (trim($params) == '') {
                $params = 'stdClass';
            }
            return $this->row_class($params);
        }
    }

    /**
     * Retourne tous les resultat dans le fetch mode par defaut
     * @param string $params - Parametre pour le retour
     * @return mixed
     */
    public function result($params = '') {
        if ($this->fetch == 'array') {
            return $this->result_array();
        } else {
            if (trim($params) == '') {
                $params = 'stdClass';
            }
            return $this->result_class($params);
        }
    }

    /**
     * Retourne une ligne sous forme de tableau
     * @return mixed
     */
    public function row_array() {
        if ($this->statement !== null) {
            $result = $this->statement->fetch();
            if ($result === false) {
                //Si il n'y a plus de resultat on reset le requete
                $this->reset();
            } else {
                //Correction resultat doublon
                foreach ($result as $key => $val) {
                    if (is_int($key) && !is_string($key)) {
                        unset($result[$key]);
                    }
                }
            }
            return $result;
        }
        return false;
    }

    /**
     * Retourne tous les resultats sous forme de tableau de tableau
     * @return mixed
     */
    public function result_array() {
        if ($this->statement !== null) {
            $result = $this->statement->fetchAll();
            if ($result !== false) {
                //Correction resultat doublon
                foreach ($result as $num => $line) {
                    foreach ($line as $key => $val) {
                        if (is_int($key) && !is_string($key)) {
                            unset($result[$num][$key]);
                        }
                    }
                }
            }
            //On reset la requete
            $this->reset();
            return $result;
        }
        return false;
    }

    /**
     * Retourne une ligne de resultat sous forme d'objet
     * @param string $class - Le nom de la class
     * @return mixed
     */
    public function row_class($class = 'stdClass') {
        if ($this->statement !== null) {
            $result = $this->statement->fetchObject($class);
            if ($result === false) {
                //Si il n'y a plus de resultat on reset le requete
                $this->reset();
            }
            return $result;
        }
        return false;
    }

    /**
     * Renvoie tous les resultas sous forme d'un tableau d'objet
     * @param string $class - Le nom de la class
     * @return mixed
     */
    public function result_class($class = 'stdClass') {
        if ($this->statement !== null) {
            $result = $this->statement->fetchAll(PDO::FETCH_CLASS, $class);
            //On reset la requete
            $this->reset();
            return $result;
        }
        return false;
    }

     /**
     * Securise et execute une requete sql
     * @param string $sql - Une requete sql, si vide prend celle de la class
     * @param boolean $excep - Renvoie ou non l'exception si il y en a une (renvoie false sinon)
     * @param boolean $statement - Renvoie le PDOstatement si pas d'erreur (sinon renvoie le resultat de pdostm->execute())
     * @return boolean|string|PDOstatement
     */
    public function execute($sql = '', $excep = false, $statement = false) {
        //Si il y a une requete en parametre on l'execute
        if (trim($sql) != '') {
            try {
                $this->statement = $this->pdo->prepare($sql);
                if($statement){
                    $this->statement->execute();
                    return $this->statement;
                }
                return $this->statement->execute();
            } catch (Exception $ex) {
                if($excep){
                    return $ex->getMessage();
                }
                return false;
            }
        }
        //Sinon on exceute la requete de l'instance
        else if (trim($this->requete) != '') {
            try {
                $this->statement = $this->pdo->prepare($this->requete);
                if($statement){
                    $this->statement->execute();
                    return $this->statement;
                }
                return $this->statement->execute();
            } catch (Exception $ex) {
                if($excep){
                    return $ex->getMessage();
                }
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Securise et execute une requete sql
     * @param string $sql - Une requete sql, si vide prend celle de la class
     * @return this|null this en cas de succes, null sinon
     */
    public function exec($sql = '') {
        if ($this->execute($sql)) {
            return $this;
        } else {
            return null;
        }
    }
    
    /* === Méthodes magiques === */
    
    /**
     * Méthode magique appelée quand l'objet est utilisé comme un fonction : $db(param)
     * Execute un requete sql et retourne le resultat
     * @param string $sql - Requete sql à executer
     * @return false|mixed
     */
    public function __invoke($sql) {
        if($this->execute($sql) === false){
            return false;
        }
        return $this->result();
    }
    
    /**
     * Méthode magique appelée quand un parametre inconnue est utilisée
     * Retourne une instance de DatabaseTable pour manipuler la table
     * @param string $name
     * @return \DatabaseTable
     */
    public function __get($name) {
        return new DatabaseTable($this, $this->prefix, $name);
    }
    
    /**
     * Méthode magique appelée lors de l'affectation d'un parametre inconnue
     * Essaye d'aouter les données dans la table
     * @param string $name
     * @param array $value
     * @return boolean - Inutile le retour n'est jamais récupèré
     */
    public function __set($name, array $value) {
        //Regarde si on à le nom des champs
        if(isset($value[0]) && !is_array($value[0])){
            //Pas de nom pour les champs tentative d'insert
            $sql = "Insert into " . $this->prefix . $name . " Values(";
            foreach ($value as $val){
                $sql .= "'" . $val . "',";
            }
            $sql = rtrim($sql, ",") . ");";
            return $this->execute($sql);
        }
        //Tentative d'insert
        return $this->insert($name, $value);
    }
    
    /**
     * Méthode magie appelée quand une méthode inconnue est utilisée
     * Sans argument effectue un appel à $this->get($table) {@see Database::get($table)}
     * Avec un argument effectue un appel à $this->get_where($table, $where) {@see Database::get_where($table, $where)}
     * @param string $name
     * @param array $arguments
     * @return false|mixed
     */
    public function __call($name, $arguments) {
        switch(count($arguments)){
            case 0:
                return $this->get($name);
            case 1:
                return $this->get_where($name, $arguments[0]);
            default:
                return false;
        }
    }

    /* === Méthode statique === */

    public static function get_instance($dbName = '', $prefix = '') {
        if (self::$instance === null) {
            return new Database($dbName, $prefix);
        }
        return self::$instance;
    }

}