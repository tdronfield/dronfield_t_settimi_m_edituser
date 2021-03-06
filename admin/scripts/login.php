<?php

function login($username, $password, $ip)
{
    // return 'You are trying to login with U:'.$username.'P:'.$password.'.';

    // check against user DB to check credentials
    // Check if UN and PW match DB
    $pdo = Database::getInstance()->getConnection();
    $get_user_query = 'SELECT * FROM tbl_user WHERE user_name = :username AND user_pass=:password';
    $user_set = $pdo->prepare($get_user_query);
    $user_set->execute(
        array(
            ':username'=>$username,
            ':password'=>$password,
        )
    );

    // add if statement to check if IP is NULL

    //if(no ip (NULL) (user has never logged in) {
    //  redirect_to edit user page.     
    //} else {
    //  run normal login
    //}

    // Run Login
    if ($found_user = $user_set->fetch(PDO::FETCH_ASSOC)){
        // Found user, log in!
        // Debugging line only
        // return "Logging in!!";

        // Indicate the ID
        $found_user_id = $found_user['user_id'];

        // Indicate IP
        $ip = $found_user['user_ip'];

        // Indicate account_created
        $account_created = $found_user['account_created'];

        // Write user and id into session
        $_SESSION['user_id'] = $found_user_id;
        $_SESSION['user_name'] = $found_user['user_fname'];
        $_SESSION['user_level'] = $found_user['user_level'];
        $_SESSION['account_created'] = $found_user['account_created'];
        
        // If account_created is the same as today's date, nest login script
        // Else "Please Contact and Admin"
        // How to avoid this when not first time logging in?
        // Nest inside if(is_null($ip)) ? 

        // if($account_created == date("Y-m-d")){
            
        // } else {
        //     return "Account Expired. Please contact an Admin.";
        // }

        // Check if IP is NULL  
        // If it is, login like normal, but redirect to edit user page
        if (is_null($ip)){
            
            // Login like usual
            // Update user IP
            // Restore IP address in $ip variable
            $ip = $_SERVER['REMOTE_ADDR'];
            $update_user_query = 'UPDATE tbl_user SET user_ip = :user_ip WHERE user_id=:user_id';
            $update_user_set = $pdo->prepare($update_user_query);
            $update_user_set->execute(
                array(
                    ':user_ip'=>$ip,
                    ':user_id'=>$found_user_id
                )
            );

            // Redirect user to edit account
            redirect_to('admin_edituser.php');

        } else {
            // Login like usual
            // Update user IP
            $update_user_query = 'UPDATE tbl_user SET user_ip = :user_ip WHERE user_id=:user_id';
            $update_user_set = $pdo->prepare($update_user_query);
            $update_user_set->execute(
                array(
                    ':user_ip'=>$ip,
                    ':user_id'=>$found_user_id
                )
            );

            // Redirect user back to index.php
            redirect_to('index.php');
        }

    } else {
        // Invalid attemp, rejected!
        return "Learn how to type!!";
    }
}

function confirm_logged_in($admin_above_only=false)
{
    if(!isset($_SESSION['user_id'])){
        redirect_to("admin_login.php");
    }

    if(!empty($admin_above_only) && empty($_SESSION['user_level'])){
        // 0 (editor) returns empty
        redirect_to("index.php");
    }
}

function logout()
{
    session_destroy();

    redirect_to('admin_login.php');
}