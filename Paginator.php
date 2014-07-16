<?php
/**
 * Copyright 2014 Platinum Pixs, LLC. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 * http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */

namespace PlatinumPixs\SimplePagination;

class Paginator
{
    const DEFAULT_LIMIT = 25;

    const DEFAULT_MID_RANGE = 5;

    /**
     * Current Displayed Page
     *
     * @var int
     */
    private $_currentPage;

    /**
     * The total items on on the page
     *
     * @var int
     */
    private $_limit;

    /**
     * Total number of pages that will be generated
     *
     * @var int
     */
    private $_numPages;

    /**
     * Total items that are in the database
     *
     * @var int
     */
    private $_itemsCount;

    /**
     * Starting item number to be shown on page
     *
     * @var int
     */
    private $_offset;

    /**
     * Pages to show at left and right of current page
     *
     * @var int
     */
    private $_midRange;

    /**
     * Range initial page
     *
     * @var int
     */
    private $_startRange;

    /**
     * Range end page
     *
     * @var int
     */
    private $_endRange;

    /**
     * Array of page numbers to show
     *
     * @var array
     */
    private $_range = array();

    public function __construct()
    {
    }

    /**
     * Total amount of rows that is contained in the database
     *
     * @param $items
     * @return void
     */
    public function setItemCount($items)
    {
        $this->_itemsCount = abs((int)$items);
    }

    /**
     * Gets the total items that is in the database
     *
     * @return int
     */
    public function getItemCount()
    {
        return $this->_itemsCount;
    }

    /**
     * Sets the current page variable
     *
     * @param $page
     * @return void
     */
    public function setCurrentPage($page)
    {
        $this->_currentPage = abs((int)$page);
    }

    /**
     * Sets the total amount of rows to display - used to limit the db
     *
     * @param $limit
     * @return void
     */
    public function setLimit($limit)
    {
        $this->_limit = abs((int)$limit);
    }

    /**
     * Gets the database limit number
     *
     * @return int
     */
    public function getLimit()
    {
        if (!is_numeric($this->_limit))
        {
            $this->_limit = self::DEFAULT_LIMIT;
        }

        return $this->_limit;
    }

    /**
     * Sets the mid range variable, the amount of pages to show in the list
     *
     * @param $range
     * @return void
     */
    public function setMidRange($range)
    {
        $this->_midRange = abs((int)$range);
    }

    /**
     * Gets the mid range variable, the amount of pages to show in the list
     *
     * @return int
     */
    public function getMidRange()
    {
        if ($this->_midRange < 1)
        {
            $this->_midRange = self::DEFAULT_MID_RANGE;
        }

        return $this->_midRange;
    }

    /**
     * Gets the current page the user is on
     *
     * @return int
     */
    public function getCurrentPage()
    {
        if ($this->_currentPage != NULL)
        {
            return $this->_currentPage;
        }

        if (isset($_GET['page']))
        {
            $this->_currentPage = (int)$_GET['page'];
        }
        else
        {
            $this->_currentPage = 1;
        }

        return $this->_currentPage;
    }

    /**
     * Gets the total pages that are available
     *
     * @return int
     */
    public function getNumPages()
    {
        if ($this->_numPages == NULL)
        {
            $this->setNumPages();
        }

        return $this->_numPages;
    }

    /**
     * Calculates and sets the total number of the pages available
     *
     * @return void
     */
    public function setNumPages()
    {
        // If limit is set to 0 or set to number bigger then total items count display all in on page
        if (($this->_limit < 1) || ($this->_limit > $this->_itemsCount))
        {
            $this->_numPages = 1;
        }
        else
        {
            // Calculate rest numbers from dividing operations so we can add one more page for this items
            $restItemsNum = $this->_itemsCount % $this->_limit;

            // if rest items > 0 then add one more else just be limit
            $this->_numPages = $restItemsNum > 0 ?
                intval($this->_itemsCount / $this->_limit) + 1 :
                intval($this->_itemsCount / $this->_limit);
        }
    }

    /**
     * Calculates the array that is used to display the page numbers
     *
     * @return void
     */
    public function setRange()
    {
        $currentPage = $this->getCurrentPage();
        $numPages = $this->getNumPages();
        $midRange = $this->getMidRange();

        $this->_startRange = $currentPage - floor($midRange / 2);
        $this->_endRange = $currentPage + floor($midRange / 2);

        if ($this->_startRange <= 0)
        {
            $this->_endRange += abs($this->_startRange) + 1;
            $this->_startRange = 1;
        }

        if ($this->_endRange > $numPages)
        {
            $this->_startRange -= $this->_endRange - $numPages;
            $this->_endRange = $numPages;
        }


        $this->_range = range($this->_startRange, $this->_endRange);
    }

    /**
     * Gets the range of page numbers to display
     *
     * @return array
     */
    public function getRange()
    {
        if (empty($this->_range))
        {
            $this->setRange();
        }

        return $this->_range;
    }

    /**
     * Creates the proper url to advances or go back to a previous page
     *
     * @param  string $url        The current url of the page
     * @param  string $pageNumber The new page number
     * @return string
     */
    public function createUrl($url, $pageNumber)
    {
        $url = preg_replace('/page=(\d+)/', '', $url);

        // page might have been the only thing in the url once removed leaves a false ?, trim it out
        $url = rtrim($url, '?');

        if (strpos($url, '?') === FALSE)
        {
            $fullUrl = $url . '?page=' . $pageNumber;
        }
        else
        {
            $fullUrl = $url . '&page=' . $pageNumber;
        }

        return $fullUrl;
    }

    /**
     * Calculates and sets the offset used in db calculations
     *
     * @return void
     */
    public function setOffset()
    {
        $this->_offset = (int)($this->getCurrentPage() - 1) * $this->_limit;
    }

    /**
     * Gets the offset of the db rows to find
     *
     * @return int
     */
    public function getOffset()
    {
        if ($this->_offset == NULL)
        {
            $this->setOffset();
        }

        return $this->_offset;
    }

    /**
     * Gets the beginning number of the count information
     *
     * @return int
     */
    public function getCountBeginning()
    {
        return $this->getOffset() + 1;
    }

    /**
     * Gets the end number of the count information
     *
     * @return int
     */
    public function getCountEnd()
    {
        $retval = $this->getOffset() + $this->getLimit();

        if ($retval > $this->getItemCount())
        {
            $retval = $this->getItemCount();
        }

        return $retval;
    }
}
 