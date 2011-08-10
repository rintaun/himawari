<?php

/**
 * General static methods around Ant style pattern selectors.
 */
class PurSelector{
	
	/**
	 * Match an Ant style pattern against a string.
	 * 
	 * From http://ant.apache.org/manual/dirtasks.html
	 * 
	 * Patterns are used for the inclusion and exclusion of files. These patterns look 
	 * very much like the patterns used in DOS and UNIX:
	 * 
	 * -   '*' matches zero or more characters
	 * -   '?' matches one character.
	 * 
	 * In general, patterns are considered relative paths, 
	 * relative to a task dependent base directory (the dir attribute in the case of <fileset>). 
	 * Only files found below that base directory are considered. So while a pattern 
	 * like ../foo.java is possible, it will not match anything when applied since the base 
	 * directory's parent is never scanned for files.
	 * 
	 * @return 
	 * @param $path Object
	 * @param $patter Object
	 */
	public static function match($pattern, $path, $isCaseSensitive=false){
        //$patArr = pattern.toCharArray();
        $patArr = $pattern;
        //$pathArr = str.toCharArray();
        $pathArr = $path;
        $patIdxStart = 0;
        $patIdxEnd = strlen($patArr) - 1;
        $pathIdxStart = 0;
        $pathIdxEnd = strlen($pathArr) - 1;
        $ch;

        $containsStar = false;
        for ($i = 0; $i < strlen($patArr); $i++) {
            if ($patArr[$i] == '*') {
                $containsStar = true;
                break;
            }
        }

        if (!$containsStar) {
            // No '*'s, so we make a shortcut
            if ($patIdxEnd != $pathIdxEnd) {
                return false; // Pattern and string do not have the same size
            }
            for ($i = 0; $i <= $patIdxEnd; $i++) {
                $ch = $patArr[$i];
                if ($ch != '?') {
                    if ($isCaseSensitive && $ch != $pathArr[$i]) {
                        return false; // Character mismatch
                    }
                    if (!$isCaseSensitive && strtoupper($ch)
                            != strtoupper($pathArr[$i])) {
                        return false;  // Character mismatch
                    }
                }
            }
            return true; // String matches against pattern
        }

        if ($patIdxEnd == 0) {
            return true; // Pattern contains only '*', which matches anything
        }
        // Process characters before first star
        while (($ch = $patArr[$patIdxStart]) != '*' && $pathIdxStart <= $pathIdxEnd) {
            if ($ch != '?') {
                if ($isCaseSensitive && $ch != $pathArr[$pathIdxStart]) {
                    return false; // Character mismatch
                }
                if (!$isCaseSensitive && strtoupper($ch)
                        != strtoupper($pathArr[$pathIdxStart])) {
                    return false; // Character mismatch
                }
            }
            $patIdxStart++;
            $pathIdxStart++;
        }
        if ($pathIdxStart > $pathIdxEnd) {
            // All characters in the string are used. Check if only '*'s are
            // left in the pattern. If so, we succeeded. Otherwise failure.
            for ($i = $patIdxStart; $i <= $patIdxEnd; $i++) {
                if ($patArr[$i] != '*') {
                    return false;
                }
            }
            return true;
        }

        // Process characters after last star
        while (($ch = $patArr[$patIdxEnd]) != '*' && $pathIdxStart <= $pathIdxEnd) {
            if ($ch != '?') {
                if ($isCaseSensitive && $ch != $pathArr[$pathIdxEnd]) {
                    return false; // Character mismatch
                }
                if (!$isCaseSensitive && strtoupper($ch)
                        != strtoupper($pathArr[$pathIdxEnd])) {
                    return false; // Character mismatch
                }
            }
            $patIdxEnd--;
            $pathIdxEnd--;
        }
        if ($pathIdxStart > $pathIdxEnd) {
            // All characters in the string are used. Check if only '*'s are
            // left in the pattern. If so, we succeeded. Otherwise failure.
            for ($i = $patIdxStart; $i <= $patIdxEnd; $i++) {
                if ($patArr[$i] != '*') {
                    return false;
                }
            }
            return true;
        }

        // process pattern between stars. padIdxStart and patIdxEnd point
        // always to a '*'.
        while ($patIdxStart != $patIdxEnd && $pathIdxStart <= $pathIdxEnd) {
            $patIdxTmp = -1;
            for ($i = $patIdxStart + 1; $i <= $patIdxEnd; $i++) {
                if ($patArr[$i] == '*') {
                    $patIdxTmp = $i;
                    break;
                }
            }
            if ($patIdxTmp == $patIdxStart + 1) {
                // Two stars next to each other, skip the first one.
                $patIdxStart++;
                continue;
            }
            // Find the pattern between padIdxStart & padIdxTmp in str between
            // strIdxStart & strIdxEnd
            $patLength = ($patIdxTmp - $patIdxStart - 1);
            $pathLength = ($pathIdxEnd - $pathIdxStart + 1);
            $foundIdx = -1;
            //strLoop:
            for ($i = 0; $i <= $pathLength - $patLength; $i++) {
                for ($j = 0; $j < $patLength; $j++) {
                    $ch = $patArr[$patIdxStart + $j + 1];
                    if ($ch != '?') {
                        if ($isCaseSensitive && $ch != $pathArr[$pathIdxStart + $i
                                + $j]) {
                            continue 2;
                            //continue strLoop;
                        }
                        if (!$isCaseSensitive
                            && strtoupper($ch)
                                != strtoupper($pathArr[$pathIdxStart + $i + $j])) {
                            continue 2;
                            //continue strLoop;
                        }
                    }
                }
                $foundIdx = $pathIdxStart + $i;
                break;
            }

            if ($foundIdx == -1) {
                return false;
            }

            $patIdxStart = $patIdxTmp;
            $pathIdxStart = $foundIdx + $patLength;
        }

        // All characters in the string are used. Check if only '*'s are left
        // in the pattern. If so, we succeeded. Otherwise failure.
        for ($i = $patIdxStart; $i <= $patIdxEnd; $i++) {
            if ($patArr[$i] != '*') {
                return false;
            }
        }
        return true;
	}
	
	/**
	 * Match an Ant style pattern against a file path.
	 * 
	 * Use "**" to match zero or more directories.
	 * 
	 * @return boolean True if the provided path math the provided pattern
	 * @param object $pattern
	 * @param object $path
	 * @param object $isCaseSensitive[optional]
	 */
    public static function matchPath($pattern, $path, $isCaseSensitive=false) {
    	if(is_array($pattern)){
    		if(empty($pattern)) return true;
    		// Return true if no pattern provided in array or if all provided patterns match provided string
    		foreach($pattern as $p){
    			if(self::matchPath($p,$path,$isCaseSensitive)) return true;
    		}
			return false;
    	}else if(!is_string($pattern)) throw new InvalidArgumentException('Invalid Argument "pattern": string or array of strings expected');
        $patDirs = explode('/',$pattern);
        $pathDirs = explode('/',$path);

        $patIdxStart = 0;
        $patIdxEnd = count($patDirs) - 1;
        $pathIdxStart = 0;
        $pathIdxEnd = count($pathDirs) - 1;

        // up to first '**'
        while ($patIdxStart <= $patIdxEnd && $pathIdxStart <= $pathIdxEnd) {
            $patDir = $patDirs[$patIdxStart];
            if ($patDir == "**") {
                break;
            }
            if (!self::match($patDir, $pathDirs[$pathIdxStart], $isCaseSensitive)) {
                $patDirs = null;
                $pathDirs = null;
                return false;
            }
            $patIdxStart++;
            $pathIdxStart++;
        }
        if ($pathIdxStart > $pathIdxEnd) {
            // String is exhausted
            for ($i = $patIdxStart; $i <= $patIdxEnd; $i++) {
                if ($patDirs[$i] != "**") {
                    $patDirs = null;
                    $pathDirs = null;
                    return false;
                }
            }
            return true;
        } else {
            if ($patIdxStart > $patIdxEnd) {
                // String not exhausted, but pattern is. Failure.
                $patDirs = null;
                $pathDirs = null;
                return false;
            }
        }

        // up to last '**'
        while ($patIdxStart <= $patIdxEnd && $pathIdxStart <= $pathIdxEnd) {
            $patDir = $patDirs[$patIdxEnd];
            if ($patDir == "**") {
                break;
            }
            if (!self::match($patDir, $pathDirs[$pathIdxEnd], $isCaseSensitive)) {
                $patDirs = null;
                $pathDirs = null;
                return false;
            }
            $patIdxEnd--;
            $pathIdxEnd--;
        }
        if ($pathIdxStart > $pathIdxEnd) {
            // String is exhausted
            for ($i = $patIdxStart; $i <= $patIdxEnd; $i++) {
                if ($patDirs[$i]!="**") {
                    $patDirs = null;
                    $pathDirs = null;
                    return false;
                }
            }
            return true;
        }

        while ($patIdxStart != $patIdxEnd && $pathIdxStart <= $pathIdxEnd) {
            $patIdxTmp = -1;
            for ($i = $patIdxStart + 1; $i <= $patIdxEnd; $i++) {
                if ($patDirs[$i] == "**") {
                    $patIdxTmp = $i;
                    break;
                }
            }
            if ($patIdxTmp == $patIdxStart + 1) {
                // '**/**' situation, so skip one
                $patIdxStart++;
                continue;
            }
            // Find the pattern between padIdxStart & padIdxTmp in str between
            // strIdxStart & strIdxEnd
            $patLength = ($patIdxTmp - $patIdxStart - 1);
            $pathLength = ($pathIdxEnd - $pathIdxStart + 1);
            $foundIdx = -1;
            //$pathLoop:
                        for ($i = 0; $i <= $pathLength - $patLength; $i++) {
                            for ($j = 0; $j < $patLength; $j++) {
                                $subPat = $patDirs[$patIdxStart + $j + 1];
                                $subStr = $pathDirs[$pathIdxStart + $i + $j];
                                if (!self::match($subPat, $subStr, $isCaseSensitive)) {
                                    //continue $pathLoop;
                                    continue 2;
                                }
                            }

                            $foundIdx = $pathIdxStart + $i;
                            break;
                        }

            if ($foundIdx == -1) {
                $patDirs = null;
                $pathDirs = null;
                return false;
            }

            $patIdxStart = $patIdxTmp;
            $pathIdxStart = $foundIdx + $patLength;
        }

        for ($i = $patIdxStart; $i <= $patIdxEnd; $i++) {
            if ($patDirs[$i]!="**") {
                $patDirs = null;
                $pathDirs = null;
                return false;
            }
        }

        return true;
    }
	
}
