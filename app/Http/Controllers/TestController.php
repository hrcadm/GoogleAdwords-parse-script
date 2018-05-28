<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class TestController extends Controller
{
    /**
     *  Rename the function as pleased and put in
     *  corresponding controller of yours
     *
     * @param  Request $request
     * @return $finalResult
     */
    public function test(Request $request)
    {
        // This needs to be changed to read from Cache, not a Form (this was test only)
    	$json = file_get_contents($request->file('jsonData'));
        //////////////////////////////////////////////////////////

        /////////// Don't change here onwards
    	$dec = json_decode($json, true);

        // Get the list of all words in array Query key separately
        // dd($combinedWordArray); to see output
    	foreach($dec['data'] as $key => $value)
    	{
    		foreach($value as $k => $v)
    		{
                $wordArrays = array_map(function ($v)
                    {
                        return explode(" ", $v["Query"]);
                    }, $dec['data']);

                $combinedWordArray = call_user_func_array('array_merge', $wordArrays);
			}
    	}
        //////////////////////////////////////////////////////

        // Initiate empty array
        $dataArray = [];

        // Handling unwanted words/integers
        // dd($dataArray); to see output
        foreach($combinedWordArray as $k => $v)
        {
            if(strlen($v) < 3 || preg_match('~[0-9]~', $v) || $v == '')
            {
                // don't include number values and words shorter then 3 words into results
            } else {
                array_push($dataArray, $v);
            }
        }
        ////////////////////////////////////

        // Final Result array
        $finalResult = [];

        // Need to count how many Array objects are present
        $entries = count($dataArray);

        // Loop for as many entries there are
        for ($i=0 ; $i <= $entries;) {
            $finalResult = $this->loopThroughWordsAndObjects($dec,$finalResult,$dataArray);
            $i++;
        }

        // count words to loop through
        $count = count($finalResult);

        for ($i=0; $i <= $count; $i++) {
            $finalResult = $this->calculateSums($finalResult);
        }

        return response()->json($finalResult);
    }

    /**
     *  Assemble array with given results
     *
     * @param  $finalResult
     * @param  $word
     * @param  $query
     * @param  $value
     * @return $finalResult
     */
    function array_push_multi_assoc($finalResult, $word, $query, $value)
    {
        $finalResult[$word][$query] = $value;

        return $finalResult;
    }

    /**
     *  Loop through every word and every object
     *
     * @param  $dec
     * @param  $finalResult
     * @param  $dataArray
     * @return $finalResult
     */
    function loopThroughWordsAndObjects($dec,$finalResult,$dataArray)
    {
        // Getting the Json objects to loop separately
        foreach($dec['data'] as $index => $string)
        {
            // Getting the words to loop separately
            foreach($dataArray as $word)
            {
                $query = $string['Query'];

                // if word exists in "Query" key, push it into the array
                if(strpos($query, $word) !== false)
                {
                    $finalResult = $this->array_push_multi_assoc($finalResult, $word, $query, $value=$string);
                }
            }
        }

        return $finalResult;
    }


    function calculateSums($finalResult)
    {
        /// Looping trough each nested object and it's key=>value pairs to find each element value
        /// (cost, clicks etc.) and sum it app and add to an array under each word
        foreach($finalResult as $key => $arr)
        {
            $number = $clicks = $cost = 0;
            $value = 0.00;
            foreach ($arr as $item) {
                $value += floatval($item['Conversions']);
                $number += intval($item['Impressions']);
                $clicks += intval($item['Clicks']);
                $cost += intval($item['Cost']);
            }
            $finalResult[$key]['Conversions'] = floatval($value);
            $finalResult[$key]['Impressions'] = $number;
            $finalResult[$key]['Clicks'] = $clicks;
            $finalResult[$key]['Cost'] = $cost;
        }

        return $finalResult;
    }
}
