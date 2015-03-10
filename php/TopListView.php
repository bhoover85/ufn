<?php

class TopListView {
  private $date;
  private $model;

  public function __construct($memcache, $model, $date) {
    $this->memcache = $memcache;
    $this->model = $model;
    $this->date = $date;
  }

  /**************************************************************************/
  /* Displays user input form to filter movies by year.                     */
  /* If no date is entered, display top 10 movies from database. Data will  */
  /* be pulled from cache if valid, otherwise the db will be queried.       */
  /* If a date has been entered, display filtered list of movies.           */
  /**************************************************************************/
  public function output() {
    $html = '<a href="/">Show Top 10 Movies</a>
             <form action="?action=filter" method="post">
             <input name="date" type="hidden" value="' . $this->date .'"/>
             <label>Filter Movies by Year</label>
             <input name="date" type="text" value="" maxlength="4" size="4"/> 
             <input type="submit" value="Submit"/>
             </form>';

     echo $html;

     // If no date has been entered, get top 10. Otherwise get filtered list.
     if ($this->model->getDate($date) == "") {
      echo '<h1>IMDb Top 10 Movies</h1>';
      $this->displayList($this->model->getTop250($this->memcache));
     }
     else {
      echo '<h1>IMDb Top 10 Movies for '.$this->model->getDate().' </h1>';

      if ($this->model->getFilteredList() != "") {
        $this->displayList($this->model->getFilteredList());
      }
      else {
        echo "No movies found.";
      }
      
     }
  }

  // Outputs table of movies to page (max of 10).
  private function displayList($array) {
    $i=0;
    echo '<div class="CSSTableGenerator" >
            <table >
              <tr>
                <td>
                  Rank
                </td>
                <td >
                  Title
                </td>
                <td>
                  Year
                </td>
                <td>
                  Rating
                </td>
                <td>
                  Number of Votes
                </td>
              </tr>';
    foreach ($array as $var) {
      if ($i<10) {
        $data = '<tr>
                  <td >
                    '.$var['rank'].'
                  </td>
                  <td>
                    '.$var['title'].'
                  </td>
                  <td>
                    '.$var['year'].'
                  </td>
                  <td>
                    '.$var['rating'].'
                  </td>
                  <td>
                    '.$var['votes'].'
                  </td>
                </tr>';
        echo $data;
      }
      else {
        break;
      }
      $i++;
    }
    echo '</table></div>';
  }
}

?>