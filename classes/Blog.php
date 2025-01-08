<?php

class Blog
{
     private $pdo;

     public function __construct(Database $database)
     {
          $this->pdo = $database->connect();
     }

     public function create()
     {
          $sql = "INSERT INTO clients () VALUES ()";
          $stmt = $this->pdo->prepare($sql);
          return $stmt->execute([
              
          ]);
     }

     public function getByUserId($id)
     {
          $sql = "SELECT * FROM clients WHERE id = :id";
          $stmt = $this->pdo->prepare($sql);
          $stmt->execute(['id' => $id]);
          return $stmt->fetch(PDO::FETCH_ASSOC);
     }

     public function update()
     {
       
     }

     public function delete($id)
     {
          $sql = "DELETE FROM clients WHERE id = :id";
          $stmt = $this->pdo->prepare($sql);
          return $stmt->execute(['id' => $id]);
     }

     public function getAll()
     {
          $sql = "SELECT * FROM blogs";
          $stmt = $this->pdo->query($sql);
          return $stmt->fetchAll(PDO::FETCH_ASSOC);
     }
}
