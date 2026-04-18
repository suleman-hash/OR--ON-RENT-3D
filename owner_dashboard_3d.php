<?php
session_start();
if(!isset($_SESSION['owner_id'])){header("Location: owner_login_3d.php");exit;}
$conn=new mysqli("localhost","root","","or_onrent");
$owner_id=$_SESSION['owner_id'];
$owner_name=$_SESSION['owner_name'];
$add_msg="";
if(isset($_POST['add_listing'])){
  $cat=$conn->real_escape_string($_POST['category']);
  $type=$conn->real_escape_string($_POST['type']);
  $price=$conn->real_escape_string($_POST['price']);
  $pt=$conn->real_escape_string($_POST['pricing_type']);
  $desc=$conn->real_escape_string($_POST['description']);
  $city=$conn->real_escape_string($_POST['city']);
  $area=$conn->real_escape_string($_POST['area']);
  $drv=isset($_POST['driver_included'])?1:0;
  $img="";
  if(!empty($_FILES['image']['name'])){$up="uploads/";if(!is_dir($up))mkdir($up,0755,true);$ext=pathinfo($_FILES['image']['name'],PATHINFO_EXTENSION);$img=$up.uniqid().'.'.$ext;move_uploaded_file($_FILES['image']['tmp_name'],$img);}
  $sql="INSERT INTO listings(owner_id,category,type,price,pricing_type,description,image,city,area,driver_included,status,date_added) VALUES('$owner_id','$cat','$type','$price','$pt','$desc','$img','$city','$area','$drv','pending',NOW())";
  $add_msg=$conn->query($sql)===TRUE?'ok':'err';
}
if(isset($_GET['delete'])){$lid=intval($_GET['delete']);$conn->query("DELETE FROM listings WHERE id=$lid AND owner_id=$owner_id");header("Location: owner_dashboard_3d.php");exit;}
$total=$conn->query("SELECT COUNT(*) c FROM listings WHERE owner_id=$owner_id")->fetch_assoc()['c'];
$pending=$conn->query("SELECT COUNT(*) c FROM listings WHERE owner_id=$owner_id AND status='pending'")->fetch_assoc()['c'];
$bookings=$conn->query("SELECT COUNT(*) c FROM bookings b JOIN listings l ON b.listing_id=l.id WHERE l.owner_id=$owner_id")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Owner Dashboard — OR On Rent</title>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;900&family=Raleway:wght@300;400;500;600;700&family=Cormorant+Garamond:ital,wght@0,300;1,300&display=swap" rel="stylesheet"/>
<style>
:root{--void:#04030a;--deep:#080614;--surface:#0e0b1f;--card:rgba(255,255,255,0.04);--border:rgba(201,150,60,0.15);--gold:#c9963c;--gold-lt:#f0c060;--gold-shine:linear-gradient(135deg,#f0c060 0%,#c9963c 40%,#8b6520 70%,#f0c060 100%);--platinum:#ede8f8;--silver:#b0a8c8;--mist:#7068a0;--rose-gold:#c97060;--green:#5ac88a;--red:#e88a7a;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body{background:var(--void);color:var(--platinum);font-family:'Raleway',sans-serif;display:flex;min-height:100vh;cursor:none;}
#cur{position:fixed;width:10px;height:10px;background:var(--gold);border-radius:50%;pointer-events:none;z-index:999;transform:translate(-50%,-50%);box-shadow:0 0 12px var(--gold);mix-blend-mode:screen;}

/* SIDEBAR */
.sidebar{width:260px;min-height:100vh;background:linear-gradient(180deg,rgba(255,255,255,0.04) 0%,rgba(255,255,255,0.02) 100%);border-right:1px solid var(--border);padding:30px 20px;display:flex;flex-direction:column;gap:6px;position:sticky;top:0;height:100vh;overflow-y:auto;backdrop-filter:blur(20px);}
.sb-logo{font-family:'Cinzel',serif;font-size:1.4rem;font-weight:900;letter-spacing:4px;background:var(--gold-shine);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin-bottom:6px;}
.sb-sub{font-size:0.6rem;letter-spacing:3px;text-transform:uppercase;color:var(--mist);margin-bottom:28px;padding-bottom:20px;border-bottom:1px solid var(--border);}
.sb-label{font-family:'Cinzel',serif;font-size:0.55rem;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--mist);padding:0 8px;margin-top:16px;margin-bottom:4px;opacity:0.7;}
.nav-i{display:flex;align-items:center;gap:10px;padding:11px 14px;border-radius:10px;color:var(--mist);font-family:'Raleway',sans-serif;font-size:0.8rem;font-weight:600;cursor:none;transition:all .3s;text-decoration:none;border:none;background:none;width:100%;text-align:left;position:relative;}
.nav-i::before{content:'';position:absolute;left:0;top:4px;bottom:4px;width:2px;background:var(--gold-shine);border-radius:2px;transform:scaleY(0);transition:transform .3s;}
.nav-i:hover,.nav-i.active{background:rgba(201,150,60,0.08);color:var(--gold-lt);}
.nav-i:hover::before,.nav-i.active::before{transform:scaleY(1);}
.nav-i-icon{font-size:1.1rem;width:22px;text-align:center;}
.sb-owner{margin-top:auto;padding:18px 12px;border-top:1px solid var(--border);display:flex;align-items:center;gap:12px;}
.sb-av{width:40px;height:40px;border-radius:50%;background:rgba(201,150,60,0.15);border:1px solid rgba(201,150,60,0.3);display:flex;align-items:center;justify-content:center;font-size:1.1rem;}
.sb-name{font-family:'Cinzel',serif;font-size:0.78rem;font-weight:700;color:var(--platinum);}
.sb-role{font-size:0.62rem;color:var(--mist);}

/* MAIN */
.main{flex:1;padding:40px 44px;overflow-x:hidden;}
.page-h{margin-bottom:40px;}
.page-t{font-family:'Cinzel',serif;font-size:2.2rem;font-weight:700;color:var(--platinum);letter-spacing:1px;margin-bottom:4px;}
.page-s{font-family:'Cormorant Garamond',serif;font-style:italic;font-size:0.95rem;color:var(--silver);}
.panel{display:none;}.panel.active{display:block;}

/* STATS */
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:20px;margin-bottom:36px;}
.stat-c{background:linear-gradient(135deg,rgba(255,255,255,0.05) 0%,rgba(255,255,255,0.02) 100%);border:1px solid var(--border);border-radius:18px;padding:26px 24px;position:relative;overflow:hidden;transition:transform .3s,box-shadow .3s;}
.stat-c::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:var(--gold-shine);}
.stat-c:hover{transform:translateY(-4px);box-shadow:0 20px 50px rgba(0,0,0,0.5),0 0 30px rgba(201,150,60,0.1);}
.stat-n{font-family:'Cinzel',serif;font-size:2.6rem;font-weight:900;background:var(--gold-shine);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;line-height:1;}
.stat-l{font-family:'Raleway',sans-serif;font-size:0.65rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--mist);margin-top:8px;}

/* TABLE */
.tbl-wrap{background:linear-gradient(135deg,rgba(255,255,255,0.04) 0%,rgba(255,255,255,0.01) 100%);border:1px solid var(--border);border-radius:18px;overflow:hidden;margin-bottom:28px;}
.tbl-hd{padding:20px 26px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;}
.tbl-hd-t{font-family:'Cinzel',serif;font-size:0.82rem;font-weight:700;color:var(--platinum);letter-spacing:1px;}
table{width:100%;border-collapse:collapse;}
th{font-family:'Cinzel',serif;font-size:0.55rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--mist);padding:12px 22px;text-align:left;border-bottom:1px solid rgba(201,150,60,0.08);}
td{padding:13px 22px;font-size:0.82rem;color:var(--silver);border-bottom:1px solid rgba(255,255,255,0.03);transition:background .2s;}
tr:hover td{background:rgba(201,150,60,0.03);}
tr:last-child td{border-bottom:none;}
.badge{display:inline-block;padding:3px 12px;border-radius:20px;font-family:'Cinzel',serif;font-size:0.6rem;font-weight:700;letter-spacing:1px;}
.badge.pending{background:rgba(201,150,60,0.1);color:var(--gold);border:1px solid rgba(201,150,60,0.25);}
.badge.approved{background:rgba(90,200,138,0.1);color:var(--green);border:1px solid rgba(90,200,138,0.25);}
.badge.rejected{background:rgba(232,138,122,0.1);color:var(--red);border:1px solid rgba(232,138,122,0.25);}
.ab{padding:5px 14px;border-radius:8px;font-family:'Cinzel',serif;font-size:0.6rem;font-weight:700;letter-spacing:1px;cursor:none;transition:all .25s;text-decoration:none;display:inline-block;margin-right:6px;border:none;}
.ab.del{background:rgba(232,138,122,0.08);color:var(--red);border:1px solid rgba(232,138,122,0.2);}
.ab.del:hover{background:var(--red);color:#fff;}
.ab.add-btn{background:rgba(201,150,60,0.1);color:var(--gold);border:1px solid rgba(201,150,60,0.25);}
.ab.add-btn:hover{background:rgba(201,150,60,0.2);}

/* FORM CARD */
.form-card{background:linear-gradient(135deg,rgba(255,255,255,0.05) 0%,rgba(255,255,255,0.02) 100%);border:1px solid var(--border);border-radius:20px;padding:36px;max-width:720px;}
.gold-bar{height:2px;background:linear-gradient(90deg,transparent,var(--gold),var(--gold-lt),var(--gold),transparent);border-radius:2px;margin-bottom:24px;}
.fc-t{font-family:'Cinzel',serif;font-size:1.5rem;font-weight:700;color:var(--platinum);margin-bottom:6px;}
.fc-s{font-family:'Cormorant Garamond',serif;font-style:italic;font-size:0.92rem;color:var(--silver);margin-bottom:28px;}
.fg{margin-bottom:20px;}
.lbl{display:block;font-family:'Cinzel',serif;font-size:0.58rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gold);margin-bottom:8px;}
.inp{width:100%;padding:12px 16px;background:rgba(255,255,255,0.04);border:1px solid rgba(201,150,60,0.18);border-radius:10px;color:var(--platinum);font-family:'Raleway',sans-serif;font-size:0.88rem;outline:none;transition:all .3s;}
.inp::placeholder{color:var(--mist);}
.inp:focus{border-color:var(--gold);box-shadow:0 0 0 3px rgba(201,150,60,0.1);}
.inp-ta{resize:vertical;min-height:90px;}
.fr2{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
.fr3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;}
.chk-row{display:flex;align-items:center;gap:10px;background:rgba(255,255,255,0.03);border:1px solid rgba(201,150,60,0.15);padding:12px 16px;border-radius:10px;cursor:none;}
.chk-row input{accent-color:var(--gold);width:16px;height:16px;}
.chk-label{font-family:'Raleway',sans-serif;font-size:0.82rem;font-weight:600;color:var(--silver);}
.sub-btn{padding:14px 36px;background:var(--gold-shine);border:none;border-radius:12px;color:#0e0800;font-family:'Cinzel',serif;font-weight:700;font-size:0.72rem;letter-spacing:2px;text-transform:uppercase;cursor:none;transition:all .35s;box-shadow:0 6px 28px rgba(201,150,60,0.35);}
.sub-btn:hover{transform:translateY(-2px);box-shadow:0 10px 36px rgba(201,150,60,0.55);}
.alrt{padding:13px 18px;border-radius:10px;font-size:0.8rem;font-family:'Raleway',sans-serif;font-weight:600;margin-bottom:22px;display:flex;align-items:center;gap:8px;}
.alrt.ok{background:rgba(90,200,138,0.1);border:1px solid rgba(90,200,138,0.25);color:var(--green);}
.alrt.err{background:rgba(232,138,122,0.1);border:1px solid rgba(232,138,122,0.25);color:var(--red);}
.prof-row{display:flex;flex-direction:column;gap:16px;max-width:480px;}
.prof-field-lbl{font-family:'Cinzel',serif;font-size:0.58rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gold);margin-bottom:7px;}
.prof-field-val{padding:12px 16px;background:rgba(255,255,255,0.04);border:1px solid rgba(201,150,60,0.15);border-radius:10px;font-size:0.88rem;color:var(--silver);}
@media(max-width:900px){.sidebar{display:none;}.main{padding:24px 16px;}.fr2,.fr3{grid-template-columns:1fr;}}
</style>
</head>
<body>
<div id="cur"></div>
<aside class="sidebar">
  <div class="sb-logo">OR</div>
  <div class="sb-sub">Owner Dashboard</div>
  <div class="sb-label">Main</div>
  <button class="nav-i active" onclick="sp('overview',this)"><span class="nav-i-icon">◈</span> Overview</button>
  <button class="nav-i" onclick="sp('listings',this)"><span class="nav-i-icon">◉</span> My Listings</button>
  <button class="nav-i" onclick="sp('add',this)"><span class="nav-i-icon">✦</span> Add Listing</button>
  <button class="nav-i" onclick="sp('bookings',this)"><span class="nav-i-icon">◷</span> Bookings</button>
  <div class="sb-label">Account</div>
  <button class="nav-i" onclick="sp('profile',this)"><span class="nav-i-icon">◌</span> My Profile</button>
  <a class="nav-i" href="OR-3D-index.html"><span class="nav-i-icon">◎</span> View Site</a>
  <a class="nav-i" href="owner_logout.php"><span class="nav-i-icon">⊗</span> Sign Out</a>
  <div class="sb-owner">
    <div class="sb-av">👤</div>
    <div><div class="sb-name"><?=htmlspecialchars($owner_name)?></div><div class="sb-role">Service Owner</div></div>
  </div>
</aside>
<div class="main">

  <!-- OVERVIEW -->
  <div class="panel active" id="panel-overview">
    <div class="page-h"><div class="page-t">Dashboard</div><div class="page-s">Welcome back, <?=htmlspecialchars($owner_name)?>. Here is your account overview.</div></div>
    <div class="stats">
      <div class="stat-c"><div class="stat-n"><?=$total?></div><div class="stat-l">Total Listings</div></div>
      <div class="stat-c"><div class="stat-n"><?=$pending?></div><div class="stat-l">Pending Approval</div></div>
      <div class="stat-c"><div class="stat-n"><?=$bookings?></div><div class="stat-l">Total Bookings</div></div>
      <div class="stat-c"><div class="stat-n">₹0</div><div class="stat-l">Total Earnings</div></div>
    </div>
    <div class="tbl-wrap">
      <div class="tbl-hd"><span class="tbl-hd-t">Recent Listings</span><button class="ab add-btn" onclick="sp('add',null)">+ Add New</button></div>
      <table>
        <thead><tr><th>Type</th><th>Category</th><th>Price</th><th>City</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        <?php $r=$conn->query("SELECT * FROM listings WHERE owner_id=$owner_id ORDER BY date_added DESC LIMIT 5");
        if($r&&$r->num_rows>0):while($row=$r->fetch_assoc()):?>
          <tr><td><?=htmlspecialchars($row['type'])?></td><td><?=htmlspecialchars($row['category'])?></td><td>₹<?=htmlspecialchars($row['price'])?></td><td><?=htmlspecialchars($row['city'])?></td><td><span class="badge <?=$row['status']?>"><?=ucfirst($row['status'])?></span></td><td><a class="ab del" href="?delete=<?=$row['id']?>" onclick="return confirm('Delete this listing?')">Delete</a></td></tr>
        <?php endwhile;else:?><tr><td colspan="6" style="text-align:center;color:var(--mist);padding:28px;">No listings yet. <a href="#" onclick="sp('add',null)" style="color:var(--gold);">Add your first →</a></td></tr><?php endif;?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- LISTINGS -->
  <div class="panel" id="panel-listings">
    <div class="page-h"><div class="page-t">My Listings</div><div class="page-s">All your listed services and vehicles on OR.</div></div>
    <div class="tbl-wrap">
      <div class="tbl-hd"><span class="tbl-hd-t">All Listings (<?=$total?>)</span></div>
      <table>
        <thead><tr><th>#</th><th>Type</th><th>Category</th><th>Pricing</th><th>Price</th><th>City/Area</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        <?php $all=$conn->query("SELECT * FROM listings WHERE owner_id=$owner_id ORDER BY date_added DESC");$i=1;
        if($all&&$all->num_rows>0):while($row=$all->fetch_assoc()):?>
          <tr><td><?=$i++?></td><td><?=htmlspecialchars($row['type'])?></td><td><?=htmlspecialchars($row['category'])?></td><td><?=htmlspecialchars($row['pricing_type'])?></td><td>₹<?=htmlspecialchars($row['price'])?></td><td><?=htmlspecialchars($row['city'])?> / <?=htmlspecialchars($row['area'])?></td><td><span class="badge <?=$row['status']?>"><?=ucfirst($row['status'])?></span></td><td><a class="ab del" href="?delete=<?=$row['id']?>" onclick="return confirm('Delete?')">Delete</a></td></tr>
        <?php endwhile;else:?><tr><td colspan="8" style="text-align:center;color:var(--mist);padding:28px;">No listings found.</td></tr><?php endif;?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ADD LISTING -->
  <div class="panel" id="panel-add">
    <div class="page-h"><div class="page-t">Add Listing</div><div class="page-s">List a new service or vehicle for rent on OR.</div></div>
    <?php if($add_msg==='ok'):?><div class="alrt ok">✦ Listing submitted! Visible after admin approval.</div><?php elseif($add_msg==='err'):?><div class="alrt err">✦ Error submitting. Please try again.</div><?php endif;?>
    <div class="form-card">
      <div class="gold-bar"></div>
      <div class="fc-t">Service Details</div>
      <p class="fc-s">Complete all fields for faster admin approval.</p>
      <form method="POST" enctype="multipart/form-data">
        <div class="fr2">
          <div class="fg"><label class="lbl">Category</label><select class="inp" name="category" style="cursor:none;" required><option value="">Select Category</option><option>Labour &amp; Services</option><option>Vehicles &amp; Services</option><option>Marriage &amp; Services</option></select></div>
          <div class="fg"><label class="lbl">Service / Vehicle Type</label><input class="inp" type="text" name="type" placeholder="e.g. Electrician, Honda City, Caterer" required/></div>
        </div>
        <div class="fr3">
          <div class="fg"><label class="lbl">Price (₹)</label><input class="inp" type="number" name="price" placeholder="0" required/></div>
          <div class="fg"><label class="lbl">Pricing Type</label><select class="inp" name="pricing_type" style="cursor:none;" required><option value="">Select</option><option>Half Day</option><option>Full Day</option><option>Hourly</option><option>Per KM / Distance</option><option>Per Plate</option><option>Per Event</option></select></div>
          <div class="fg"><label class="lbl">Upload Image</label><input class="inp" type="file" name="image" accept="image/*" style="padding:9px;"/></div>
        </div>
        <div class="fr2">
          <div class="fg"><label class="lbl">City</label><select class="inp" name="city" style="cursor:none;" required><option value="">Select City</option><option>Pune</option><option>Mumbai</option><option>Nashik</option><option>Nagpur</option></select></div>
          <div class="fg"><label class="lbl">Area / Pincode</label><input class="inp" type="text" name="area" placeholder="e.g. Pimpri, 411017" required/></div>
        </div>
        <div class="fg"><label class="lbl">Description</label><textarea class="inp inp-ta" name="description" placeholder="Describe your service, experience, availability..." required></textarea></div>
        <div class="fg"><label class="chk-row"><input type="checkbox" name="driver_included"/><span class="chk-label">Driver / Operator Included in Price</span></label></div>
        <button class="sub-btn" type="submit" name="add_listing">Submit Listing ✦</button>
      </form>
    </div>
  </div>

  <!-- BOOKINGS -->
  <div class="panel" id="panel-bookings">
    <div class="page-h"><div class="page-t">Bookings</div><div class="page-s">Bookings received for your listed services.</div></div>
    <div class="tbl-wrap">
      <div class="tbl-hd"><span class="tbl-hd-t">Recent Bookings</span></div>
      <table>
        <thead><tr><th>#</th><th>Service</th><th>Category</th><th>Date</th><th>Payment</th></tr></thead>
        <tbody>
        <?php $bk=$conn->query("SELECT b.*,l.type,l.category FROM bookings b JOIN listings l ON b.listing_id=l.id WHERE l.owner_id=$owner_id ORDER BY b.booking_date DESC LIMIT 10");
        $i=1;if($bk&&$bk->num_rows>0):while($row=$bk->fetch_assoc()):?>
          <tr><td><?=$i++?></td><td><?=htmlspecialchars($row['type'])?></td><td><?=htmlspecialchars($row['category'])?></td><td><?=htmlspecialchars($row['booking_date'])?></td><td><span class="badge <?=$row['payment_status']==='paid'?'approved':'pending'?>"><?=ucfirst($row['payment_status'])?></span></td></tr>
        <?php endwhile;else:?><tr><td colspan="5" style="text-align:center;color:var(--mist);padding:28px;">No bookings yet.</td></tr><?php endif;?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- PROFILE -->
  <div class="panel" id="panel-profile">
    <div class="page-h"><div class="page-t">My Profile</div><div class="page-s">Your registered owner account details.</div></div>
    <?php $o=$conn->query("SELECT * FROM owners WHERE id=$owner_id")->fetch_assoc();?>
    <div class="form-card" style="max-width:500px;">
      <div class="gold-bar"></div>
      <div class="prof-row">
        <?php foreach(['name'=>'Full Name','email'=>'Email','mobile'=>'Mobile','city'=>'City','area'=>'Area'] as $k=>$label):?>
        <div><div class="prof-field-lbl"><?=$label?></div><div class="prof-field-val"><?=htmlspecialchars($o[$k]??'—')?></div></div>
        <?php endforeach;?>
        <a href="owner_login_3d.php" style="display:inline-block;padding:12px 28px;background:rgba(232,138,122,0.1);border:1px solid rgba(232,138,122,0.25);color:var(--red);font-family:'Cinzel',serif;font-weight:700;font-size:0.7rem;letter-spacing:2px;border-radius:10px;text-decoration:none;text-align:center;transition:all .3s;">Sign Out ✦</a>
      </div>
    </div>
  </div>

</div>
<script>
const cur=document.getElementById('cur');document.addEventListener('mousemove',e=>{cur.style.left=e.clientX+'px';cur.style.top=e.clientY+'px';});
function sp(id,btn){
  document.querySelectorAll('.panel').forEach(p=>p.classList.remove('active'));
  document.getElementById('panel-'+id).classList.add('active');
  if(btn){document.querySelectorAll('.nav-i').forEach(b=>b.classList.remove('active'));btn.classList.add('active');}
}
</script>
</body>
</html>
