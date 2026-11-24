<?php
declare(strict_types=1);
function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function is_logged_in(): bool { return isset($_SESSION['user_id']); }
function current_user_role(PDO $pdo): string {
    if (!is_logged_in()) return 'guest';
    $stmt=$pdo->prepare('SELECT role FROM users WHERE id=:id LIMIT 1'); $stmt->execute([':id'=>(int)$_SESSION['user_id']]); $role=$stmt->fetchColumn();
    if (!is_string($role)) { $_SESSION=[]; session_destroy(); redirect('login.php'); }
    $_SESSION['role']=$role; return $role;
}
function require_login(): void { if (!is_logged_in()) redirect('login.php'); }
function require_role(PDO $pdo,array $allowedRoles): void { require_login(); if(!in_array(current_user_role($pdo),$allowedRoles,true)){http_response_code(403);exit('Access denied.');} }
function can_manage_post(PDO $pdo,int $ownerId): bool { if(!is_logged_in())return false; $role=current_user_role($pdo); return in_array($role,['admin','editor'],true)||(int)$_SESSION['user_id']===$ownerId; }
function validate_post_input(string $title,string $content,string $category,string $tags): array {
    $errors=[]; if($title==='')$errors[]='Title is required.'; elseif(mb_strlen($title)>200)$errors[]='Title must be 200 characters or fewer.';
    if($content==='')$errors[]='Content is required.'; elseif(mb_strlen($content)>50000)$errors[]='Content must be 50,000 characters or fewer.';
    if(!in_array($category,['General','Technology','Lifestyle','Education','Business','Travel','Health','News'],true))$errors[]='Choose a valid category.';
    if(mb_strlen($tags)>500)$errors[]='Tags must be 500 characters or fewer.'; return $errors;
}
function csrf_token(): string { if(empty($_SESSION['csrf_token']))$_SESSION['csrf_token']=bin2hex(random_bytes(32)); return $_SESSION['csrf_token']; }
function verify_csrf(): void { $token=$_POST['csrf_token']??''; if(!is_string($token)||!hash_equals(csrf_token(),$token)){http_response_code(400);exit('Invalid request token. Refresh and try again.');} }
function redirect(string $url): never { header('Location: '.$url); exit; }
function upload_featured_image(array $file,?string $current=null): array {
    if(!isset($file['error'])||$file['error']===UPLOAD_ERR_NO_FILE)return [null,null];
    if($file['error']!==UPLOAD_ERR_OK)return [null,'Image upload failed. Please try again.'];
    if($file['size']>3*1024*1024)return [null,'Image must be 3 MB or smaller.'];
    $info=@getimagesize($file['tmp_name']); if(!$info)return [null,'Please upload a valid image file.'];
    $mime=$info['mime']??''; $extensions=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    if(!isset($extensions[$mime]))return [null,'Only JPG, PNG and WebP images are allowed.'];
    $dir=__DIR__.'/uploads'; if(!is_dir($dir)&&!mkdir($dir,0755,true))return [null,'Upload folder could not be created.'];
    $name=bin2hex(random_bytes(16)).'.'.$extensions[$mime];
    if(!move_uploaded_file($file['tmp_name'],$dir.'/'.$name))return [null,'Could not save the uploaded image.'];
    if($current && preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/',$current) && is_file($dir.'/'.$current))@unlink($dir.'/'.$current);
    return [$name,null];
}
