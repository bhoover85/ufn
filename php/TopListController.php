<?php

class TopListController {
  private $model;
  private $movieList;
  private $filteredList;

  public function __construct($model) {
    $this->model = $model;
  }

  /**************************************************************************/
  /* Generates array of movies based on user input and stores in model.     */
  /* Retrieves movie db from model before filtering. Model will return      */
  /* cached data if still valid, otherwise it will query the database.      */
  /**************************************************************************/
  public function filter($memcache, $date) {
    $this->model->setDate($date);
    $list = $this->model->getTop250($memcache);

    $i=0;
    foreach ($list as $array) {
      if ($date['date'] == $array['year']) {
        $this->filteredList[$i]['rank']   = $array['rank'];
        $this->filteredList[$i]['title']  = $array['title'];
        $this->filteredList[$i]['year']   = $array['year'];
        $this->filteredList[$i]['rating'] = $array['rating'];
        $this->filteredList[$i]['votes']  = $array['votes'];
        $i++;
      }
    }

    // Stores array of filtered movies in model.
    $this->model->setFilteredList($this->filteredList);
  }

  /**************************************************************/
  /* Reads IMDb Top 250 page into array to prepare for parsing. */
  /* If cache is still valid, does not pull data from IMDb.     */
  /**************************************************************/
  public function getTop250($memcache) {
    if (!$memcache->get('movies')) {
      $url = 'http://www.imdb.com/chart/top';
      $page = file($url);

      $this->movieList = $this->parseTop250($page);
      $this->model->setTop250($memcache, $this->movieList);
    }
  }

  /**************************************************************************/
  /* Parses IMDb Top 250 page array.                                        */
  /* Returns new array populated with Rank, Title, Year, Rating and Votes.  */
  /**************************************************************************/
  private function parseTop250($page) {
    $i=0;
    foreach ($page as $key => $value) {
      $rank   = '#>(.*?)\.</span>#';
      $title  = '#>(.*?)</a>#';
      $year   = '#>\((.*?)\)</span>#';
      $rating = '#title="(.*?)based#';
      $votes  = '#based on(.*?)votes#';

      // Stores movie rank
      if (preg_match($rank, $value, $matches)) {
        $movieList[$i]['rank'] = $matches[1];
      }

      // Stores movie title
      if (substr($value, 0, 5) == 'title') {
        if (preg_match($title, $value, $matches) && ($matches[1] != "IMDb") && ($matches[1] != "")) {
          $movieList[$i]['title'] = $matches[1];
        }
      }

      // Stores movie year
      if (preg_match($year, $value, $matches)) {
        $movieList[$i]['year'] = $matches[1];
      }

      // Stores movie rating
      if (substr($value, 4, 17) == '<strong name="nv"') {
        if (preg_match($rating, $value, $matches)) {
          $movieList[$i]['rating'] = str_replace(' ', '', $matches[1]);
        }
      }

      // Stores movie votes
      if (substr($value, 4, 17) == '<strong name="nv"') {
        if (preg_match($votes, $value, $matches)) {
          $movieList[$i]['votes'] = str_replace(' ', '', $matches[1]);
          $i++;
        }
      }
    }

    return $movieList;
  }
}

?>