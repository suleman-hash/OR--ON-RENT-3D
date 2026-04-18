<?php
session_start();
$servername="localhost"; $username_db="root"; $password_db=""; $dbname="or_onrent";
$conn = new mysqli($servername,$username_db,$password_db,$dbname);
$error_msg=""; $success_msg="";
if(isset($_POST['register'])){
  $name=$conn->real_escape_string(trim($_POST['name']));
  $email=$conn->real_escape_string(trim($_POST['email']));
  $mobile=$conn->real_escape_string(trim($_POST['mobile']));
  $city=$conn->real_escape_string(trim($_POST['city']));
  $area=$conn->real_escape_string(trim($_POST['area']));
  $pass=password_hash($_POST['password'],PASSWORD_DEFAULT);
  $check=$conn->query("SELECT id FROM owners WHERE email='$email'");
  if($check->num_rows>0){$error_msg="Email already registered.";}
  else{
    $sql="INSERT INTO owners(name,email,mobile,password,city,area) VALUES('$name','$email','$mobile','$pass','$city','$area')";
    if($conn->query($sql)===TRUE){$success_msg="Registration successful! Please sign in.";} else{$error_msg="Error: ".$conn->error;}
  }
}
if(isset($_POST['login'])){
  $email=$conn->real_escape_string(trim($_POST['email']));
  $pass=$_POST['password'];
  $result=$conn->query("SELECT * FROM owners WHERE email='$email'");
  if($result->num_rows>0){
    $row=$result->fetch_assoc();
    if(password_verify($pass,$row['password'])){
      $_SESSION['owner_id']=$row['id']; $_SESSION['owner_name']=$row['name'];
      header("Location: owner_dashboard_3d.php"); exit;
    } else {$error_msg="Invalid password.";}
  } else {$error_msg="No account found with this email.";}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Owner Portal — OR On Rent</title>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;900&family=Raleway:wght@300;400;500;600;700&family=Cormorant+Garamond:ital,wght@0,300;1,300&display=swap" rel="stylesheet"/>
<style>
:root{--void:#04030a;--deep:#080614;--card:rgba(255,255,255,0.05);--border:rgba(201,150,60,0.2);--gold:#c9963c;--gold-lt:#f0c060;--gold-shine:linear-gradient(135deg,#f0c060 0%,#c9963c 40%,#8b6520 70%,#f0c060 100%);--platinum:#ede8f8;--silver:#b0a8c8;--mist:#7068a0;--rose-gold:#c97060;--text:#ede8f8;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{background:var(--void);color:var(--text);font-family:'Raleway',sans-serif;min-height:100vh;overflow-x:hidden;cursor:none;}
canvas{position:fixed;inset:0;z-index:0;pointer-events:none;}
#cur{position:fixed;width:12px;height:12px;background:var(--gold);border-radius:50%;pointer-events:none;z-index:999;transform:translate(-50%,-50%);box-shadow:0 0 16px var(--gold);mix-blend-mode:screen;}
nav{position:relative;z-index:10;display:flex;align-items:center;justify-content:space-between;padding:22px 48px;border-bottom:1px solid rgba(201,150,60,0.1);background:rgba(4,3,10,0.8);backdrop-filter:blur(20px);}
.nav-logo{font-family:'Cinzel',serif;font-size:1.4rem;font-weight:900;letter-spacing:6px;background:var(--gold-shine);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;text-decoration:none;}
.nav-back{font-family:'Raleway',sans-serif;font-size:0.72rem;font-weight:600;letter-spacing:2px;color:var(--mist);text-decoration:none;transition:color .3s;}
.nav-back:hover{color:var(--gold-lt);}
main{position:relative;z-index:2;min-height:calc(100vh - 80px);display:flex;align-items:center;justify-content:center;padding:60px 20px;}
.orb{position:absolute;border-radius:50%;filter:blur(80px);pointer-events:none;}
.orb1{width:500px;height:500px;background:radial-gradient(circle,rgba(201,150,60,0.12) 0%,transparent 70%);top:-150px;right:-100px;}
.orb2{width:350px;height:350px;background:radial-gradient(circle,rgba(100,60,180,0.1) 0%,transparent 70%);bottom:-80px;left:-60px;}
.auth-wrap{width:100%;max-width:500px;position:relative;z-index:2;}
.auth-crown{text-align:center;margin-bottom:40px;}
.crown-logo{font-family:'Cinzel',serif;font-size:2.8rem;font-weight:900;letter-spacing:6px;background:var(--gold-shine);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;line-height:1;margin-bottom:6px;}
.crown-sub{font-family:'Cinzel',serif;font-size:0.55rem;font-weight:600;letter-spacing:8px;text-transform:uppercase;color:var(--mist);}
.crown-line{height:1px;background:linear-gradient(90deg,transparent,var(--gold),transparent);margin:20px auto;max-width:200px;}
.tabs{display:flex;background:rgba(255,255,255,0.03);border:1px solid rgba(201,150,60,0.12);border-radius:50px;padding:4px;margin-bottom:32px;}
.tab-btn{flex:1;padding:11px;background:none;border:none;color:var(--mist);font-family:'Cinzel',serif;font-size:0.68rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;cursor:none;border-radius:40px;transition:all .35s;}
.tab-btn.active{background:var(--gold-shine);color:#0e0800;box-shadow:0 4px 20px rgba(201,150,60,0.4);}
.card-3d{background:linear-gradient(135deg,rgba(255,255,255,0.06) 0%,rgba(255,255,255,0.02) 100%);border:1px solid rgba(201,150,60,0.15);border-radius:24px;padding:40px;backdrop-filter:blur(30px);box-shadow:0 40px 80px rgba(0,0,0,0.6),0 0 0 1px rgba(255,255,255,0.05),inset 0 1px 0 rgba(255,255,255,0.08);}
.gold-bar{height:2px;background:linear-gradient(90deg,transparent,var(--gold),var(--gold-lt),var(--gold),transparent);border-radius:2px;margin-bottom:28px;}
.panel{display:none;}.panel.active{display:block;}
.panel-title{font-family:'Cinzel',serif;font-size:1.6rem;font-weight:700;color:var(--platinum);margin-bottom:6px;}
.panel-sub{font-family:'Cormorant Garamond',serif;font-style:italic;font-size:0.95rem;color:var(--silver);margin-bottom:28px;}
.alert{padding:12px 18px;border-radius:12px;font-family:'Raleway',sans-serif;font-size:0.8rem;font-weight:600;margin-bottom:22px;display:flex;align-items:center;gap:8px;}
.alert.err{background:rgba(201,112,96,0.1);border:1px solid rgba(201,112,96,0.25);color:var(--rose-gold);}
.alert.ok{background:rgba(46,180,100,0.1);border:1px solid rgba(46,180,100,0.25);color:#5ac88a;}
.fg{margin-bottom:18px;}
.lbl{display:block;font-family:'Cinzel',serif;font-size:0.58rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gold);margin-bottom:8px;}
.inp{width:100%;padding:13px 18px;background:rgba(255,255,255,0.04);border:1px solid rgba(201,150,60,0.18);border-radius:10px;color:var(--platinum);font-family:'Raleway',sans-serif;font-size:0.88rem;outline:none;transition:all .3s;}
.inp::placeholder{color:var(--mist);}
.inp:focus{border-color:var(--gold);box-shadow:0 0 0 3px rgba(201,150,60,0.1);}
.fr{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.submit{width:100%;padding:15px;background:var(--gold-shine);border:none;border-radius:12px;color:#0e0800;font-family:'Cinzel',serif;font-weight:700;font-size:0.75rem;letter-spacing:2px;text-transform:uppercase;cursor:none;transition:all .35s;margin-top:6px;box-shadow:0 6px 30px rgba(201,150,60,0.4);position:relative;overflow:hidden;}
.submit::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,transparent 30%,rgba(255,255,255,0.35) 50%,transparent 70%);transform:translateX(-150%);transition:transform .5s;}
.submit:hover{transform:translateY(-2px);box-shadow:0 12px 40px rgba(201,150,60,0.6);}
.submit:hover::before{transform:translateX(150%);}
footer{position:relative;z-index:2;background:rgba(4,3,10,0.9);border-top:1px solid rgba(201,150,60,0.08);padding:20px 48px;text-align:center;}
.fc{font-size:0.72rem;color:var(--mist);}
.fc span{color:var(--gold);}
@media(max-width:600px){.fr{grid-template-columns:1fr;}.card-3d{padding:28px 20px;}nav{padding:18px 20px;}}
</style>
</head>
<body>
<canvas id="pc"></canvas>
<div id="cur"></div>
<nav>
  <a href="OR-3D-index.html" class="nav-logo">OR</a>
  <a href="OR-3D-index.html" class="nav-back">← Return to Site</a>
</nav>
<main>
  <div class="orb orb1"></div><div class="orb orb2"></div>
  <div class="auth-wrap">
    <div class="auth-crown">
      <div class="crown-logo">OR</div>
      <div class="crown-sub">Owner Portal</div>
      <div class="crown-line"></div>
    </div>
    <div class="tabs">
      <button class="tab-btn <?=!isset($_POST['register'])?'active':''?>" onclick="sw('login')">Sign In</button>
      <button class="tab-btn <?=isset($_POST['register'])?'active':''?>" onclick="sw('reg')">Register</button>
    </div>
    <?php if($error_msg):?><div class="alert err">✦ <?=htmlspecialchars($error_msg)?></div><?php endif;?>
    <?php if($success_msg):?><div class="alert ok">✦ <?=htmlspecialchars($success_msg)?></div><?php endif;?>
    <div class="card-3d">
      <div class="panel <?=!isset($_POST['register'])?'active':''?>" id="loginPanel">
        <div class="gold-bar"></div>
        <div class="panel-title">Welcome Back</div>
        <p class="panel-sub">Sign in to your owner account to manage listings.</p>
        <form method="POST" action="">
          <div class="fg"><label class="lbl">Email Address</label><input class="inp" type="email" name="email" placeholder="owner@email.com" required/></div>
          <div class="fg"><label class="lbl">Password</label><input class="inp" type="password" name="password" placeholder="••••••••" required/></div>
          <button class="submit" type="submit" name="login">Sign In ✦</button>
        </form>
      </div>
      <div class="panel <?=isset($_POST['register'])?'active':''?>" id="regPanel">
        <div class="gold-bar"></div>
        <div class="panel-title">Create Account</div>
        <p class="panel-sub">Register as a service owner and start earning today.</p>
        <form method="POST" action="">
          <div class="fr">
            <div class="fg"><label class="lbl">Full Name</label><input class="inp" type="text" name="name" placeholder="Your name" required/></div>
            <div class="fg"><label class="lbl">Mobile</label><input class="inp" type="tel" name="mobile" placeholder="+91 XXXXXXXXXX" required/></div>
          </div>
          <div class="fg"><label class="lbl">Email Address</label><input class="inp" type="email" name="email" placeholder="your@email.com" required/></div>
          <div class="fr">
            <div class="fg"><label class="lbl">City</label><select class="inp" name="city" style="cursor:none;" required><option value="">Select City</option><option>Pune</option><option>Mumbai</option><option>Nashik</option><option>Nagpur</option></select></div>
            <div class="fg"><label class="lbl">Area / Pincode</label><input class="inp" type="text" name="area" placeholder="Area or PIN" required/></div>
          </div>
          <div class="fg"><label class="lbl">Password</label><input class="inp" type="password" name="password" placeholder="Create password" required/></div>
          <button class="submit" type="submit" name="register">Create Account ✦</button>
        </form>
      </div>
    </div>
  </div>
</main>
<footer><p class="fc">© 2025 <span>OR — On Rent</span>. All rights reserved.</p></footer>
<script>
// Particles
const cv=document.getElementById('pc'),cx=cv.getContext('2d');
function resize(){cv.width=window.innerWidth;cv.height=window.innerHeight;}
resize();window.addEventListener('resize',resize);
const pts=Array.from({length:80},()=>({x:Math.random()*cv.width,y:Math.random()*cv.height,s:Math.random()*1.2+0.3,o:Math.random()*0.3+0.05,sx:(Math.random()-.5)*.25,sy:-Math.random()*.3-.05,g:Math.random()>.7}));
function drawPts(){cx.clearRect(0,0,cv.width,cv.height);pts.forEach(p=>{p.x+=p.sx;p.y+=p.sy;if(p.y<0){p.y=cv.height;p.x=Math.random()*cv.width;}cx.beginPath();cx.arc(p.x,p.y,p.s,0,Math.PI*2);cx.fillStyle=p.g?`rgba(201,150,60,${p.o})`:`rgba(176,168,200,${p.o*.4})`;cx.fill();});requestAnimationFrame(drawPts);}drawPts();
// Cursor
const cur=document.getElementById('cur');document.addEventListener('mousemove',e=>{cur.style.left=e.clientX+'px';cur.style.top=e.clientY+'px';});
// Tabs
function sw(tab){
  document.querySelectorAll('.tab-btn').forEach((b,i)=>b.classList.toggle('active',(tab==='login'?i===0:i===1)));
  document.getElementById('loginPanel').classList.toggle('active',tab==='login');
  document.getElementById('regPanel').classList.toggle('active',tab==='reg');
}
<?php if(isset($_POST['register'])):?>sw('reg');<?php endif;?>
</script>
</body>
</html>
