<?php
/**
 * 浏览器类型探针
 */

class tools_clientprober
{
    public static $user_agent = '';
    
    /**
     * Returns information about the client user agent.
     *
     *     // Returns "Chrome" when using Google Chrome
     *     $browser = Request::user_agent('browser');
     *
     * Multiple values can be returned at once by using an array:
     *
     *     // Get the browser and platform with a single call
     *     $info = Kohana_Request::user_agent(array('browser', 'platform'));
     *
     * When using an array for the value, an associative array will be returned.
     *
     * @param   mixed   string to return: browser, version, robot, mobile, platform; or array of values
     * @return  mixed   requested information, FALSE if nothing is found
     */
    static function get_client_agent($value = array('browser', 'platform', 'mobile'))
    {
    	if (isset($_SERVER['HTTP_USER_AGENT']))
		{
					// Set the client user agent
		    tools_clientprober::$user_agent = $_SERVER['HTTP_USER_AGENT'];
		}
        if (is_array($value))
        {
            $agent = array();
            foreach ($value as $v)
            {
                // Add each key to the set
                $agent[$v] = tools_ClientProber::get_client_agent($v);
            }

            return $agent;
        }
        static $info;

        if (isset($info[$value]))
        {
            // This value has already been found
            return $info[$value];
        }
        $user_agent_conf = require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'clientprober' . DIRECTORY_SEPARATOR . 'user_agents.php';
        if ($value === 'browser' OR $value == 'version')
        {
            // Load browsers
            $browsers = $user_agent_conf['browser'];

            foreach ($browsers as $search => $name)
            {
                if (stripos(tools_ClientProber::$user_agent, $search) !== FALSE)
                {
                    // Set the browser name
                    $info['browser'] = $name;

                    if (preg_match('#'.preg_quote($search).'[^0-9.]*+([0-9.][0-9.a-z]*)#i', tools_ClientProber::$user_agent, $matches))
                    {
                        // Set the version number
                        $info['version'] = $matches[1];
                    }
                    else
                    {
                        // No version number found
                        $info['version'] = FALSE;
                    }

                    return $info[$value];
                }
            }
        }
        else
        {
            // Load the search group for this type
            $group = $user_agent_conf[$value];

            foreach ($group as $search => $name)
            {
                if (stripos(tools_ClientProber::$user_agent, $search) !== FALSE)
                {
                    // Set the value name
                    return $info[$value] = $name;
                }
            }
        }

        // The value requested could not be found
        return $info[$value] = FALSE;
    }

}
?>
