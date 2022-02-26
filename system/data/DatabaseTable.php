<?php

/*
 * TODO Ajouter fonction Where
 */

class DatabaseTable {

    /**
     * Instance de database utilisé pour communiquer avec la bdd
     * @var Database
     */
    protected $db;

    /**
     * Prefixe de la table
     * @var string
     */
    protected $prefix;

    /**
     * Nom de la table
     * @var string
     */
    protected $name;

    /**
     * Ligne selectionnée
     * @var array
     */
    protected $row = null;

    /**
     * Constructeur d'une instance de gestion d'une table d'une BDD
     * @param Database $database - L'instance de gestion de la BDD
     * @param string $prefix - Le prefixe de la table
     * @param string $tableName - Le nom de la table
     */
    public function __construct($database, $prefix, $tableName) {
        $this->db = $database;
        $this->prefix = $prefix;
        $this->name = $tableName;
    }

    /**
     * Retourne tous les champs de la table avec le where actuel
     * Si il y a une/des ligne(s) sélectionée(s) elles sont ajoutées au where
     * @param boolean $retour - Retourner le resultat (optional)
     * @return mixed
     */
    public function get($retour = true) {
        if($this->row !== null){
            return $this->db->get_where($this->name, $this->row, $retour);
        }
        return $this->db->get($this->name, $retour);
    }

    /**
     * Création de la condition where de la requete
     * Trois façon de l'utiliser
     * Passage d'un tableau avec clef = champ et valeur = valeur recherché ex : where(array('id' => '1'))
     * Passage de la clef et de la valeur en parametre ex : where('id', '1')
     * Passage de la clause where directement (sans le mot clef where) ex : where('id = 1 And email is null')
     * @param mixed $data - Les données
     * @param string $val - La valeur
     * @return this|null this en cas de succes, null sinon
     */
    public function where($data, $val = '') {
        if ($this->db->where($data, $val)) {
            return $this;
        } 
        return null;
    }

    /**
     * Retourne tous les champs de la table avec le where en parametre
     * @see Database::where()
     * @param string[]|string $where - Les champs/valeur pour le where | La 
     * clause where ecrite sans le mot clef where
     * @param boolean $retour - Retourner le resultat (optional)
     * @return false|mixed
     */
    public function get_where($where, $retour = true) {
        return $this->db->get_where($this->name, $where, $retour);
    }

    /**
     * Insert une ou plusieur ligne dans la table
     * 1 ligne $data = array('champ' => 'val', ...)
     * +1 lignes $data = array(array('champ' => 'val', ...), array(...))
     * @param mixed $data - Les données à insérer
     * @return false|mixed - False si echec, l'id de la ligne si réussie (sous forme de tableau si plusieur ligne)
     */
    public function insert($data) {
        return $this->db->insert($this->name, $data);
    }

    /**
     * Met à jour des champ de la table
     * 1 ligne $id = array('id' => 'val', ...)
     * +1 lignes $id = array(array('id' => 'val', ...), array(...))
     * Si une/des ligne(s) sont selectionnées et qu'aucun id est fourni elles seront utilisées
     * @param mixed $idOrData - Le ou les id de la table, ou la valeur de data si des lignes sont selectionnées
     * @param mixed $data - Les données a modifier array('champ' => 'val', ...) (optional)
     * @return boolean|boolean[] true ou false selon la reussite, en tableau si plusieurs update
     */
    public function update($idOrData, $data = null) {
        if ($data === null && $this->row !== null) {
            return $this->db->update($this->name, $this->row, $idOrData);
        } else if ($data === null) {
            return false;
        }
        return $this->db->update($this->name, $idOrData, $data);
    }

    /**
     * Supprime des champ de la table
     * 1 ligne $id = array('id' => 'val', ...)
     * +1 lignes $id = array(array('id' => 'val', ...), array(...))
     * Si une/des ligne(s) sont selectionnées et qu'aucun id est fourni elles seront utilisées
     * @param mixed $id - Le ou les id de la table (optional)
     * @return boolean|boolean[] true ou false selon la reussite, en tableau si plusieurs delete
     */
    public function delete($id = null) {
        if($id === null && $this->row !== null) {
            return $this->db->delete($this->name, $this->row);
        } else if ($id === null) {
            return false;
        }
        return $this->db->delete($this->name, $id);
    }

    /**
     * Selectionne une ou plusieurs ligne(s)
     * @param array $where - Tableau cheamp => valeur pour selectionner les lignes
     * @return $this
     */
    public function select_row(array $where) {
        //Verifie que le parametre est bien un tableau clef valeur
        if (is_array($where) && !isset($where[0])) {
            $this->row = $where;
        }
        return $this;
    }

    /**
     * Deselectionne la/les ligne(s)
     * @return $this
     */
    public function unselect_row() {
        $this->row = null;
        return $this;
    }

    /**
     * Equivalent à $this->get(true) {@see DatabaseTable::get($retour)}
     * @return mixed
     */
    public function __invoke() {
        return $this->get();
    }

    /**
     * Recupere toutes les valeurs d'un champ donné dans la base
     * Si il y a une/des ligne(s) selectionnée(s) ne récupère que leurs valeurs
     * @param string $name
     * @return boolean
     */
    public function __get($name) {
        $sql = "Select " . $name . " From " . $this->prefix . $this->name;
        //Si une ligne est selectionnée
        if ($this->row !== null) {
            $sql .= " Where ";
            foreach ($this->row as $champ => $val) {
                $sql .= $champ . " = '" . $val . "' And ";
            }
            $sql = rtrim($sql, "And ");
        }
        if ($this->db->execute($sql . ";") === false) {
            return false;
        }
        return $this->db->result();
    }

    /**
     * Met à jour la valeur des lignes selectionnées
     * @param string $name
     * @param string $value
     * @return boolean - Inutile le retour n'est jamais récupèré
     */
    public function __set($name, $value) {
        //Si il y a une ligne selectionnée
        if ($this->row === null) {
            return false;
        }
        //Mise à jour de la (ou des lignes) selectionnée
        return $this->db->update($this->name, $this->row, [$name => $value]);
    }

    /**
     * Selectionne les lignes correspondantes au nom de la méthode avec comme 
     * valeur son parametre 
     * @param type $name
     * @param type $arguments
     * @return type
     */
    public function __call($name, $arguments) {
        return $this->select_row([$name => $arguments[0]]);
    }

}