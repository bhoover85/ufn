<?php

class TopList {
  private $date;
  private $filteredList;

  /**************************************************************************/
  /* Initializes and loads/updates database with IMDb Top 250 rated movies. */
  /* If cache is still valid, it will not perform any db transactions.      */
  /**************************************************************************/
  public function setTop250($memcache, $array) {
    if (!$memcache->get('movies')) {
      try {
        $db = new PDO('sqlite:/tmp/IMDbTop250.sqlite3');
        $db->exec("CREATE TABLE IF NOT EXISTS Movies (id INTEGER PRIMARY KEY, rank INTEGER, title TEXT, year INTEGER, rating INTEGER, votes INTEGER)");

        // Inserts new values into db. If row exists, update the row instead of adding a new one.
        $insert = "INSERT OR REPLACE INTO Movies (id, rank, title, year, rating, votes) 
                   VALUES ((SELECT id FROM Movies WHERE title = :title), :rank, :title, :year, :rating, :votes)";
        $sql = $db->prepare($insert);

        // Binds parameters to sql placeholders.
        $sql->bindParam(':rank', $rank);
        $sql->bindParam(':title', $title);
        $sql->bindParam(':year', $year);
        $sql->bindParam(':rating', $rating);
        $sql->bindParam(':votes', $votes);

        // Executes sql statement and loads values into db.
        foreach ($array as $var) {
          $rank   = $var['rank'];
          $title  = $var['title'];
          $year   = $var['year'];
          $rating = $var['rating'];
          $votes  = $var['votes'];
     
          $sql->execute();
        }

        // Loads data into temp cache to reduce db load.
        $memcache->set('movies', $array, 0, 60);
      }
      catch(PDOException $e) {
        echo $e->getMessage();
      }

      $db = null;
    }
  }

  /**************************************************************************/
  /* Retrieves Top 250 movies from database.                                */
  /* If cache is still valid, it will return cached data, otherwise it will */
  /* pull the rows from the database.                                       */
  /**************************************************************************/
  public function getTop250($memcache) {
    if (!$memcache->get('movies')) {
      try {
        $db = new PDO('sqlite:/tmp/IMDbTop250.sqlite3');
        $result = $db->query('SELECT * FROM Movies');

        // Retrieves rows from db.
        foreach($result as $row) {
          $array[$row['id']]['rank']   = $row['rank'];
          $array[$row['id']]['title']  = $row['title'];
          $array[$row['id']]['year']   = $row['year'];
          $array[$row['id']]['rating'] = $row['rating'];
          $array[$row['id']]['votes']  = $row['votes'];
        }
      }
      catch(PDOException $e) {
        echo $e->getMessage();
      }

      $db = null;
    }
    else {
      // Returns cached data.
      $array = $memcache->get('movies');
    }

    return $array;
  }

  // Stores filtered list of movies generated from user input.
  public function setFilteredList($array) {
    $this->filteredList = $array;
  }

  // Retrieves filtered list of movies generated from user input.
  public function getFilteredList() {
    return $this->filteredList;
  }

  // Stores date from user input.
  public function setDate($date) {
    $this->date = $date;
  }

  // Retrieves date from user input.
  public function getDate($date) {
    return $this->date['date'];
  }
}

?>