<?php

// See http://blog.rodneyrehm.de/archives/16-Smarty-Whitespace-Control.html

/**
 * Smarty Whitespace Control
 *
 * {-tag}  remove white space infront of tag up to the previous non-whitespace character or beginning of the line
 *         "text \n\n\t {-tag}" -> "text \n\n{tag}"
 *         "text \n\n\t text\t {-tag}" -> "text \n\n\t text{tag}"
 * {--tag} remove white space infront of tag up to the previous non-whitespace character
 *         "text \n\n\t {--tag}" -> "text{tag}"
 *         "text \n\n\t text\t {--tag}" -> "text \n\n\t text{tag}"
 * {+-tag}
 * {-+tag} replace white space infront of tag up to the previous non-whitespace character by a single line-break
 *         "text \n\n\t {-+tag}" -> "text\n{tag}"
 *         "text \n\n\t text\t {-+tag}" -> "text \n\n\t text\n{tag}"
 *
 * {tag-}  remove white space after tag up to the next non-whitespace character or end of the line
 *         "{tag-} \n\n\t text" -> "{tag}\n\n\t text"
 *         "{tag-} text \n\n\t text" -> "{tag}text \n\n\t text"
 * {tag--} remove white space after tag up to the next non-whitespace character
 *         "{tag--} \n\n\t text" -> "{tag}text"
 *         "{tag--} text \n\n\t text" -> "{tag}text \n\n\t text"
 * {tag+-}
 * {tag-+} replace white space after tag up to the next non-whitespace character by a single line-break
 *         "{tag-+} \n\n\t text" -> "{tag}\n\ntext"
 *         "{tag-+} text \n\n\t text" -> "{tag}\n\ntext \n\n\t text"
 *
 * {tag+}  replace white space after tag up to the end of the line with an additional line-break
 *         "{tag+} \n\t text" -> "{tag}\n\n\t text"
 *         "{tag+} text \n\n\t text" -> "{tag}\n\ntext \n\n\t text"
 *
 * Any combination of the above, say {--tag+} is possible. Any + modifiers are executed before - modifiers, so
 *     "{tag+-}{--tag}" will lead to "{tag}{tag}"
 *
 * NOTE: {tag+} and {tag-+} cause two trailing \n. This is done because PHP itself throws away the first \n. 
 * So \n\n in the template will lead to \n in the output
 *
 * @param string $string raw template source
 * @param Smarty_Internal_Template $template Template instance 
 * @return string raw template source after whitespace control was applied
 * @author Rodney Rehm
 */
function smarty_prefilter_whitespace_control($string, Smarty_Internal_Template $template) {
    $ldelim = $template->smarty->left_delimiter;
    $rdelim = $template->smarty->right_delimiter;
    $_ldelim = preg_quote($ldelim);
    $_rdelim = preg_quote($rdelim);

    // remove preceeding whitepsace preserving a single line-break
    $string =  preg_replace('#\s*'. $_ldelim .'(?:-\+|\+-)#', "\n" . $ldelim, $string);
    // remove trailing whitespace preserving s single line-break
    $string =  preg_replace('#(?:\+-|-\+)'. $_rdelim .'\s*#', $rdelim . "\n\n", $string);

    // remove preceeding whitepsace
    $string =  preg_replace('#\s*'. $_ldelim .'--|[^\S\r\n]*'. $_ldelim .'-#', $ldelim, $string);
    // remove trailing whitespace
    $string =  preg_replace('#--'. $_rdelim .'\s*|-'. $_rdelim .'[^\S\r\n]*#', $rdelim, $string);

    // force trailing line-break
    $string =  preg_replace('#\+'. $_rdelim .'(?:\s*[\r\n]|[^\S\r\n]*)#', $rdelim . "\n\n", $string);

    return $string;
}
