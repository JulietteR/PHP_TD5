<?php

class Model
{
    protected $pdo;

    public function __construct(array $config)
    {
        try {
            if ($config['engine'] == 'mysql') {
                $this->pdo = new \PDO(
                    'mysql:dbname='.$config['database'].';host='.$config['host'],
                    $config['user'],
                    $config['password']
                );
                $this->pdo->exec('SET CHARSET UTF8');
            } else {
                $this->pdo = new \PDO(
                    'sqlite:'.$config['file']
                );
            }
        } catch (\PDOException $error) {
            throw new ModelException('Unable to connect to database');
        }
    }

    /**
     * Tries to execute a statement, throw an explicit exception on failure
     */
    protected function execute(\PDOStatement $query, array $variables = array())
    {
        if (!$query->execute($variables)) {
            $errors = $query->errorInfo();
            throw new ModelException($errors[2]);
        }

        return $query;
    }

    /**
     * Inserting a book in the database
     */
    public function insertBook($title, $author, $synopsis, $image, $copies)
    {
        $query = $this->pdo->prepare('INSERT INTO livres (titre, auteur, synopsis, image)
            VALUES (?, ?, ?, ?)');
        $this->execute($query, array($title, $author, $synopsis, $image));

        
       $bookId = $this->pdo->lastInsertId();
        for ($i= 0; $i<$copies; $i++) {

        $query = $this->pdo->prepare('INSERT INTO exemplaires (book_id)
             VALUES (?)');

         $this->execute($query, array($bookId));
        }

        // TODO: Créer $copies exemplaires
    }

    /**
     * Getting all the books
     */
    public function getBooks()
    {
        $query = $this->pdo->prepare('SELECT livres.* FROM livres');

        $this->execute($query);

        return $query->fetchAll();
    }

    /**
     * Getting one of the books
     */
    public function getABook($id)
    {
        $query = $this->pdo->prepare('SELECT livres.* FROM livres WHERE livres.id = :id');

        $this->execute($query, array(':id' => $id));

        return $query->fetchAll();
    }

     /**
     * Getting exemplaires
     */
    public function getExemplairesBooks()
    {
        $query = $this->pdo->prepare('SELECT * FROM exemplaires');

        $this->execute($query);

        return $query->fetchAll();
    }

    /**
     * Getting exemplaires from a book
     */

    public function getExemplairesABook($id)
    {
        $query = $this->pdo->prepare('SELECT * FROM exemplaires WHERE exemplaires.book_id = :id');

        $this->execute($query, array(':id' => $id));

        return $query->fetchAll();
    }
     public function borrowABook($personne, $exemplaires, $debut, $fin)
    {
        $query = $this->pdo->prepare('INSERT INTO emprunts (personne, exemplaire, debut, fin)
            VALUES (?, ?, ?, ?)');
        $this->execute($query, array($personne, $exemplaires, $debut, $fin));


        return $query->fetchAll();
    }

    public function returnABook($id)
    {
        $query = $this->pdo->prepare('UPDATE emprunts SET fini = 1 WHERE exemplaire = :id');
        $this->execute($query, array(':id' => $id));


        return $query->fetchAll();
    }

     public function getExemplaireBorrowed($id)
    {
        $query = $this->pdo->prepare('SELECT emprunts.* FROM emprunts WHERE fini = 0');

        $this->execute($query, array(':id' => $id));
     

        return $query->fetchAll();
    }


}
