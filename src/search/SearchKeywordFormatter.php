<?php
/**
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @date 3.09.2014
 */

namespace opus\elastic\search;

use yii\base\BaseObject;

/**
 * This class prepares search keywords for elastic query
 * Class SearchKeywordFormatter
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @package opus\elastic\search
 */
class SearchKeywordFormatter extends BaseObject
{
    /**
     * Wildcard definition
     *
     * @var string
     */
    public $wildCard = '*';

    /**
     * Maximum search term count to output
     *
     * @var int
     */
    public $maxTerms = 5;

    /**
     * Search terms map
     *
     * @var array
     */
    private $terms = [];

    /**
     * Search keyword input
     *
     * @var null
     */
    private $searchKeyword = null;

    /**
     * Wildcarded term template, leave this empty if you don't want to apply wildcards
     *
     * @var string
     */
    public $wildCardedTermTemplate = '{term}{wildcard}';

    /**
     * @param array $searchKeyword
     * @param array $config
     */
    public function __construct($searchKeyword, $config = [])
    {
        parent::__construct($config);
        $this->searchKeyword = $searchKeyword;
        $this->format();
    }

    private function format()
    {
        $this->removeSpecialCharacters();
        $this->filterUniqueKeywords();
        if (!empty($this->wildCardedTermTemplate)) {
            $this->setTermWildCards();
        }
        if (!empty($this->maxTerms) && count($this->terms) > $this->maxTerms) {
            $this->removeShortestTerms($this->maxTerms);
        }
    }

    /**
     * Removes special characters from search keyword
     * If two quotes are provided, those are not removed to support exact match searching
     */
    private function removeSpecialCharacters()
    {
        $terms = mb_strtolower($this->searchKeyword, 'utf-8');
        // match the letters, numbers and double quote with unicode modifier
        $regex = '/[^\p{L}|\p{N}|"]/u';
        if (substr_count($terms, '"') !== 2) {
            // match the letters and numbers with unicode modifier
            $regex = '/[^\p{L}|\p{N}]/u';
        }
        $this->searchKeyword = preg_replace($regex, ' ', $terms);
    }

    /**
     * Removes duplicated and empty terms
     */
    private function filterUniqueKeywords()
    {
        $terms = explode(' ', $this->searchKeyword);
        $terms = array_filter($terms);
        $uniqueTerms = array_unique($terms);
        $this->terms = $uniqueTerms;
    }

    /**
     * Applies wildcard(s) to search term based on wildCardedTermTemplate
     */
    private function setTermWildCards()
    {
        $wildCardedTerms = [];
        foreach ($this->terms as $term) {
            $wildCardedTerm = str_replace(
                '{term}',
                $term,
                $this->wildCardedTermTemplate
            );
            $wildCardedTerm = str_replace(
                '{wildcard}',
                $this->wildCard,
                $wildCardedTerm
            );
            $wildCardedTerms[] = $wildCardedTerm;
        }
        $this->terms = $wildCardedTerms;
    }

    /**
     * Removes shortest search terms from terms map
     *
     * @param $maxCount
     */
    private function removeShortestTerms($maxCount)
    {
        $longestWordLength = 0;
        $longestWord = '';
        $longestWords = [];
        for ($i = 0; $i <= $maxCount - 1; $i++) {
            $longestWordId = null;
            foreach ($this->terms as $id => $word) {
                if (strlen($word) > $longestWordLength) {
                    $longestWordLength = strlen($word);
                    $longestWord = $word;
                    $longestWordId = $id;
                }
            }
            $longestWordLength = 0;
            unset($this->terms[$longestWordId]);

            $longestWords[] = $longestWord;
        }
        $this->terms = $longestWords;
    }

    /**
     * Returns formatted terms
     * @return array
     */
    public function getTerms()
    {
        return $this->terms;
    }
}