<?php


// Helper function to sanitize input
function sanitize_input($data)
{
    return htmlspecialchars(stripslashes(trim($data)));
}

// Helper function to redirect
function redirect($url)
{
    header("Location: $url");
    exit();
}
function validate_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function check_user_authentication()
{

    // authentification login here

}

function requireAuth()
{
    if (!check_user_authentication()) {
        header("Location: login.php");
        exit();
    }
}

function tagHandler($content)
{

    // content example
    // $string = "hello this is a post about #coding and #programming";
    $tags = [];
    $words = explode(" ", $content);
    for ($i = 0; $i < sizeof($words); $i++) {
        $word = $words[$i];

        if ($words[$i][0] === "#" && strlen($word) > 1) {

            $tag = substr(strtolower($words[$i]), 1);

            if (!in_array($tag, $tags)) {
                $tags[] = $tag;
            }
            $words[$i] = "<a href='page#$tag'>" . $words[$i] . "</a>";
        }
    }

    $content = implode(" ", $words);
    
    $result = [ "tags" => $tags , "content" => $content] ;

    return $result;

}
