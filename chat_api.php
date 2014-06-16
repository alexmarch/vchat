<?php
$iuajksdv = 9846598234;
include "include.php";

//print_r($_POST);
$session_id = session_id();
if(isset($_POST["function"]) && isset($_POST["sessionid"]))
{
    if($_POST["sessionid"]==$session_id)
    {
        switch($_POST["function"])
        {
            case "update_user_credit": update_user_credits($_POST["credits"]); break;
            case "auth_client": auth_client(); break;
            case "get_user_credit": get_user_credits(); break;
            case "update_performer_state": update_performer_state($_POST["state"]); break;
            default: echo "{\"error\": \"Unknown function\"}"; break;
        }
    }
}

function auth_client()
{
    $user_id = $_SESSION['userid'];
    if($_SESSION["usertype"]=="performers")
    {
        $user_data["uid"] = $_SESSION["userid"];
        $user_data["nickname"] = $_SESSION["nickname"];
        $user_data["type"] = $_SESSION["usertype"];
        $chips_res = mysql_query("SELECT `multiplechips`, `privatechips`, `spychatchips` FROM `performers` WHERE `id`=$user_id");
        if($chips_res)
        {
            $chips = mysql_fetch_row($chips_res);
            $user_data["private_cost"]=$chips[0];
            $user_data["premium_cost"]=$chips[1];
            $user_data["voyeur_cost"]=$chips[2];
            echo json_encode($user_data);
            return true;
        }
        else
        {
            echo "{\"error\": \"".mysql_error()."\"}";
            return false;
        }

    }
    else
    {
        echo "{\"error\": \"Invalid user type\"}";
        return false;
    }
}

function update_user_credits($credits)
{
    if(!is_null($credits))
    {
        $user_id = $_SESSION['userid'];
        $query = "UPDATE `users` SET `chips`=$credits WHERE `id`=$user_id";
        if(mysql_query($query))
        {
            echo "{\"success\": \"ok\"}";
            return true;
        }
        else
        {
            echo "{\"error\": \"".mysql_error()."\"}";
            return false;
        }
    }
    else
    {
        echo "{\"error\": \"Credits value is not defined\"}";
        return false;
    }
}

function get_user_credits()
{
    if($_SESSION["usertype"]=="users")
    {
        $user_id = $_SESSION['userid'];
        $query = "SELECT `chips` FROM `users` WHERE `id`=$user_id";
        if($res=mysql_query($query))
        {
            $return["credits"] = mysql_fetch_row($res)[0];
            echo json_encode($return);
            return true;
        }
        else
        {
            echo "{\"error\": \"".mysql_error()."\"}";
            return false;
        }
    }
    else
    {
        echo "{\"error\": \"Invalid user type\"}";
        return false;
    }
}

function update_performer_state($state)
{
    if($_SESSION["usertype"]=="performers")
    {
        if(!is_null($state))
        {
            $user_id = $_SESSION["userid"];
            $query = "UPDATE `performers` SET `state`=$$state WHERE `id`=$$user_id";
            if(mysql_query($query))
            {
                echo "{\"success\": \"ok\"}";
                return true;
            }
            else
            {
                echo "{\"error\": \"".mysql_error()."\"}";
                return false;
            }
        }
        else
        {
            echo "{\"error\": \"State is undefined\"}";
            return false;
        }
    }
    else
    {
        echo "{\"error\": \"Invalid user type\"}";
        return false;
    }
}