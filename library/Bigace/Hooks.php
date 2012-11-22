<?php
/**
 * Bigace - a PHP and MySQL based Web CMS.
 *
 * LICENSE
 *
 * This source file is subject to the new GNU General Public License
 * that is bundled with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * http://www.bigace.de/license.html
 *
 * Bigace is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * @category   Bigace
 * @package    Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * The Hooks API allows us to create actions and filters and hooking functions, and methods.
 * The functions or methods will then be run when the action or filter is called.
 *
 * The API callback examples reference functions, but can be methods of classes.
 * To hook methods, you'll need to pass an array one of two ways.
 *
 * Any of the syntaxes explained in the PHP documentation for the
 * {@link http://us2.php.net/manual/en/language.pseudo-types.php#language.types.callback 'callback'}
 * type are valid.
 *
 * Also see the {@link http://codex.wordpress.org/Plugin_API Plugin API} for
 * more information and examples on how to use a lot of these functions.
 *
 * A list of all existing filters and actions can be found here:
 * {@link http://wiki.bigace.de/bigace:developer:hooks http://wiki.bigace.de/bigace:developer:hooks}
 *
 * @category   Bigace
 * @package    Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Hooks
{

    // information about executed actions
    private static $actions = array();
    // all available actions and filter stay here
    private static $filter = array();
    // unused currently
    private static $mergedFilter = array();
    // the current executed filter
    private static $currentFilter = array();

    /**
     * Hooks a function or method to a specific filter action.
     *
     * Filters are the hooks that BIGACE launches to modify text of various types
     * before adding it to the database or sending it to the browser screen. Plugins
     * can specify that one or more of its PHP functions is executed to
     * modify specific types of text at these times, using the Filter API.
     *
     * To use the API, the following code should be used to bind a callback to the filter
     * <code>
     * function example_hook($example) { echo $example; }
     *
     * add_filter('example_filter', 'example_hook');
     * </code>
     *
     * Hooked functions can take extra arguments that are set when
     * the matching do_action() or apply_filters() call is run. The <tt>$acceptedArgs
     * allow for calling functions only when the number of args match. Hooked functions
     * can take extra arguments that are set when the matching <tt>do_action()</tt> or
     * <tt>apply_filters()</tt> call is run. For example, the action <tt>comment_id_not_found</tt>
     * will pass any functions that hook onto it the ID of the requested comment.
     *
     * <strong>Note:</strong> the function will return true no matter if the function was hooked
     * fails or not. There are no checks for whether the function exists beforehand and no checks
     * to whether the <tt>$functionToAdd is even a string. It is up to you to take care and
     * this is done for optimization purposes, so everything is as quick as possible.
     *
     * Thr $priority is used to specify the order in which the functions associated with a
     * particular action are executed (default: 10). Lower numbers correspond with earlier
     * execution, and functions with the same priority are executed in the order in which
     * they were added to the action.
     *
     * @param string $tag The name of the filter to hook the <tt>$functionToAdd</tt> to.
     * @param callback $functionToAdd The callback to execute when the filter is applied.
     * @param int $priority The priority (see above for more infos)
     * @param int $acceptedArgs The number of arguments the function accept (default 1).
     * @return boolean true
     */
    public static function add_filter($tag, $functionToAdd, $priority = 10, $acceptedArgs = 1)
    {
        $idx = self::_filter_build_unique_id($tag, $functionToAdd, $priority);
        self::$filter[$tag][$priority][$idx] = array(
            'function' => $functionToAdd, 'accepted_args' => $acceptedArgs
        );
        unset(self::$mergedFilter[$tag]);
        return true;
    }

    /**
     * Call the functions added to a filter hook.
     *
     * The callback functions attached to filter hook <tt>$tag</tt> are invoked by
     * calling this function. This function can be used to create a new filter hook
     * by simply calling this function with the name of the new hook specified using
     * the <tt>$tag</a> parameter.
     *
     * The function allows for additional arguments to be added and passed to hooks.
     * <code>
     * function example_hook($string, $arg1, $arg2)
     * {
     *      //Do stuff
     *      return $string;
     * }
     * $value = apply_filters('example_filter', 'filter me', 'arg1', 'arg2');
     * </code>
     *
     * @param string $tag The name of the filter hook.
     * @param mixed $value The value on which the filters hooked to <tt>$tag</tt> are applied on.
     * @param mixed $var,... Additional variables passed to the functions hooked to <tt>$tag</tt>.
     * @return mixed The filtered value after all hooked functions are applied to it.
     */
    public static function apply_filters($tag, $value)
    {
        $args = array();
        self::$currentFilter[] = $tag;

        // Do 'all' actions first
        if (isset(self::$filter['all'])) {
            $args = func_get_args();
            self::_call_all_hook($args);
        }

        if (!isset(self::$filter[$tag])) {
            array_pop(self::$currentFilter);
            return $value;
        }

        // Sort
        if (!isset(self::$mergedFilter[$tag])) {
            ksort(self::$filter[$tag]);
            self::$mergedFilter[$tag] = true;
        }

        reset(self::$filter[$tag]);

        if (empty($args)) {
            $args = func_get_args();
        }

        do {
            foreach ((array) current(self::$filter[$tag]) as $the)
                if (!is_null($the['function'])) {
                    $args[1] = $value;
                    $value = call_user_func_array(
                        $the['function'], array_slice($args, 1, (int) $the['accepted_args'])
                    );
                }
        } while (next(self::$filter[$tag]) !== false);

        array_pop(self::$currentFilter);

        return $value;
    }

    /**
     * Calls the 'all' hook, which will process the functions hooked into it.
     *
     * The 'all' hook passes all of the arguments or parameters that were used for the
     * hook, which this function was called for.
     *
     * This function is used internally for apply_filters(), do_action(), and
     * do_action_ref_array() and is not meant to be used from outside those functions.
     * This function does not check for the existence of the all hook, so it will fail
     * unless the all hook exists prior to this function call.
     *
     * @uses Bigace_Hooks::$filter Used to process all of the functions in the 'all' hook
     *
     * @param array $args The collected parameters from the hook that was called.
     * @param string $hook Optional. The hook name that was used to call the 'all' hook.
     */
    private static function _call_all_hook($args)
    {
        reset(self::$filter['all']);
        do {
            foreach ((array) current(self::$filter['all']) as $the) {
                if (!is_null($the['function'])) {
                    call_user_func_array($the['function'], $args);
                }
            }
        } while (next(self::$filter['all']) !== false);
    }

    /**
     * Build Unique ID for storage and retrieval
     *
     * It works by checking for objects and creating an a new property in the class
     * to keep track of the object and new objects of the same class that need to be added.
     *
     * It also allows for the removal of actions and filters for objects after they
     * change class properties. It is possible to include the property self::$filter_id
     * in your class and set it to "null" or a number to bypass the workaround. However
     * this will prevent you from adding new classes and any new classes will overwrite
     * the previous hook by the same class.
     *
     * Functions and static method callbacks are just returned as strings and shouldn't
     * have any speed penalty.
     *
     * The $priority is used in counting how many hooks were applied.
     * If === false and $function is an object reference, we return the unique id
     * only if it already has one, false otherwise.
     *
     * @param string $tag Used in counting how many hooks were applied
     * @param string|array $function Used for creating unique id
     * @param int|bool $priority The priority (see above for more infos)
     * @param string $type filter or action
     * @return string Unique ID for usage as array key
     */
    private static function _filter_build_unique_id($tag, $function, $priority)
    {
        // If function then just skip all of the tests and not overwrite the following.
        if (is_string($function)) {
            return $function;
        } else if (is_object($function[0])) {
            // Object Class Calling
            $objIdx = get_class($function[0]) . $function[1];
            if (!isset($function[0]->_filter_id)) {
                if (false === $priority) {
                    return false;
                }
                $count = 0;
                if (isset(self::$filter[$tag][$priority])) {
                    $count = count((array) self::$filter[$tag][$priority]);
                }
                $function[0]->_filter_id = $count;
                $objIdx .= $count;
                unset($count);
            } else {
                $objIdx .= $function[0]->_filter_id;
            }
            return $objIdx;
        } else if (is_string($function[0])) {
            // Static Calling
            return $function[0] . $function[1];
        }
    }

    /**
     * Hooks a function on to a specific action.
     *
     * Actions are the hooks that the BIGACE core launches at specific points
     * during execution, or when specific events occur. Plugins can specify that
     * one or more of its PHP functions are executed at these points, using the
     * Action API.
     *
     * The $priority is used to specify the order in which the functions associated
     * with a particular action are executed (default: 10). Lower numbers correspond
     * with earlier execution, and functions with the same priority are executed
     * in the order in which they were added to the action.
     *
     * @uses add_filter() Adds an action. Parameter list and functionality are the same.
     *
     * @param string $tag The name of the action to which the <tt>$function_to-add</tt> is hooked.
     * @param callback $functionToAdd The name of the function you wish to be called.
     * @param int $priority optional. see above for more infos.
     * @param int $acceptedArgs optional. The number of arguments the function accept (default 1).
     */
    public static function add_action($tag, $functionToAdd, $priority = 10, $acceptedArgs = 1)
    {
        return self::add_filter($tag, $functionToAdd, $priority, $acceptedArgs);
    }

    /**
     * Execute functions hooked on a specific action hook.
     *
     * This function invokes all functions attached to action hook <tt>$tag</tt>.
     * It is possible to create new action hooks by simply calling this function,
     * specifying the name of the new hook using the <tt>$tag</tt> parameter.
     *
     * You can pass extra arguments to the hooks, much like you can with apply_filters().
     *
     * @see apply_filters() This function works similar with the exception that nothing is
     * returned and only the functions or methods are called.
     *
     * @param string $tag The name of the action to be executed.
     * @param mixed $arg,... Optional additional arguments which are passed to the hooked functions
     * @return null Will return null if $tag does not exist in self::$filter array
     */
    public static function do_action($tag, $arg = '')
    {
        if (is_array(self::$actions)) {
            self::$actions[] = $tag;
        } else {
            self::$actions = array($tag);
        }

        self::$currentFilter[] = $tag;

        // Do 'all' actions first
        if (isset(self::$filter['all'])) {
            $allArgs = func_get_args();
            self::_call_all_hook($allArgs);
        }

        if (!isset(self::$filter[$tag])) {
            array_pop(self::$currentFilter);
            return;
        }

        $args = array();
        if (is_array($arg) && 1 == count($arg) && is_object($arg[0])) { // array(&$this)
            $args[] = & $arg[0];
        } else {
            $args[] = $arg;
        }

        for ($a = 2; $a < func_num_args(); $a++) {
            $args[] = func_get_arg($a);
        }

        // Sort
        if (!isset(self::$mergedFilter[$tag])) {
            ksort(self::$filter[$tag]);
            self::$mergedFilter[$tag] = true;
        }

        reset(self::$filter[$tag]);

        do {
            foreach ((array) current(self::$filter[$tag]) as $the) {
                if (!is_null($the['function'])) {
                    call_user_func_array(
                        $the['function'], array_slice($args, 0, (int) $the['accepted_args'])
                    );
                }
            }
        } while (next(self::$filter[$tag]) !== false);

        array_pop(self::$currentFilter);
    }

    /**
     * Return the name of the current filter or action.
     *
     * @return string Hook name of the current filter or action.
     */
    public static function current_filter()
    {
        return end(self::$currentFilter);
    }

    /**
     * Check if any filter has been registered for a hook.
     *
     * @param string $tag The name of the filter hook.
     * @param callback $functionToCheck If specified, return the priority of that function on this hook or false if not attached.
     * @return int|boolean Optionally returns the priority on that hook for the specified function.
     */
    public static function has_filter($tag, $functionToCheck = false)
    {
        $has = !empty(self::$filter[$tag]);
        if (false === $functionToCheck || false == $has) {
            return $has;
        }

        if (!$idx = self::_filter_build_unique_id($tag, $functionToCheck, false)) {
            return false;
        }

        foreach (array_keys(self::$filter[$tag]) as $priority) {
            if (isset(self::$filter[$tag][$priority][$idx])) {
                return $priority;
            }
        }

        return false;
    }

    /**
     * Check if any action has been registered for a hook.
     *
     * @param string $tag The name of the action hook.
     * @param callback $functionToCheck If specified, return the priority of that function on this hook or false if not attached.
     * @return int|boolean Optionally returns the priority on that hook for the specified function.
     */
    public static function has_action($tag, $functionToCheck = false)
    {
        return self::has_filter($tag, $functionToCheck);
    }

    /**
     * Return the number times an action is fired.
     *
     * @param string $tag The name of the action hook.
     * @return int The number of times action hook <tt>$tag</tt> is fired
     */
    public static function did_action($tag)
    {
        if (empty(self::$actions)) {
            return 0;
        }

        return count(array_keys(self::$actions, $tag));
    }

    /**
     * Removes a function from a specified action hook.
     *
     * This function removes a function attached to a specified action hook. This
     * method can be used to remove default functions attached to a specific filter
     * hook and possibly replace them with a substitute.
     *
     * @param string $tag The action hook to which the function to be removed is hooked.
     * @param callback $functionToRemove The name of the function which should be removed.
     * @param int $priority optional The priority of the function (default: 10).
     * @param int $acceptedArgs optional. The number of arguments the function accpets (default: 1).
     * @return boolean Whether the function is removed.
     */
    public static function remove_action($tag, $functionToRemove, $priority = 10, $acceptedArgs = 1)
    {
        return self::remove_filter($tag, $functionToRemove, $priority, $acceptedArgs);
    }

    /**
     * Removes a function from a specified filter hook.
     *
     * This function removes a function attached to a specified filter hook. This
     * method can be used to remove default functions attached to a specific filter
     * hook and possibly replace them with a substitute.
     *
     * To remove a hook, the <tt>$functionToRemove</tt> and <tt>$priority</tt> arguments
     * must match when the hook was added. This goes for both filters and actions. No warning
     * will be given on removal failure.
     *
     * @param string $tag The filter hook to which the function to be removed is hooked.
     * @param callback $functionToRemove The name of the function which should be removed.
     * @param int $priority optional. The priority of the function (default: 10).
     * @param int $acceptedArgs optional. The number of arguments the function accpets (default: 1).
     * @return boolean Whether the function existed before it was removed.
     */
    public static function remove_filter($tag, $functionToRemove, $priority = 10, $acceptedArgs = 1)
    {
        $functionToRemove = self::_filter_build_unique_id($tag, $functionToRemove, $priority);

        $r = isset(self::$filter[$tag][$priority][$functionToRemove]);

        if (true === $r) {
            unset(self::$filter[$tag][$priority][$functionToRemove]);
            if (empty(self::$filter[$tag][$priority])) {
                unset(self::$filter[$tag][$priority]);
            }
            unset(self::$mergedFilter[$tag]);
        }

        return $r;
    }

}