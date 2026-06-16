<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: products.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $slug = strtolower(str_replace(' ', '-', $name));
    $description = $_POST['description'];
    $price = (float)$_POST['price'];
    $category = $_POST['category'];
    $image_url = $_POST['image_url'];
    $sizes = !empty($_POST['sizes']) ? json_encode(explode(',', $_POST['sizes'])) : json_encode([]);
    $colors = !empty($_POST['colors']) ? json_encode(explode(',', $_POST['colors'])) : json_encode([]);
    $stock = (int)$_POST['stock'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_new = isset($_POST['is_new']) ? 1 : 0;
    
    try {
        $stmt = $pdo->prepare("UPDATE products SET name=?, slug=?, description=?, price=?, category=?, image_url=?, sizes=?, colors=?, stock=?, is_featured=?, is_new=? WHERE id=?");
        $stmt->execute([$name, $slug, $description, $price, $category, $image_url, $sizes, $colors, $stock, $is_featured, $is_new, $id]);
        $success = "Product updated successfully!";
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

$sizes_list = implode(',', json_decode($product['sizes'], true) ?? []);
$colors_list = implode(',', json_decode($product['colors'], true) ?? []);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Edit Product - Style Rwanda Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f0f2f5; }
        
        .sidebar { width: 280px; background: #000; color: white; position: fixed; height: 100%; left: 0; top: 0; }
        .sidebar-header { padding: 25px; text-align: center; border-bottom: 1px solid #222; }
        .sidebar-header h2 { color: #D4AF37; }
        .sidebar-nav { padding: 20px 0; }
        .sidebar-nav a { display: flex; align-items: center; gap: 15px; padding: 12px 25px; color: #ccc; text-decoration: none; }
        .sidebar-nav a:hover { background: #D4AF37; color: #000; }
        .main-content { margin-left: 280px; padding: 25px; }
        
        .form-container { background: white; padding: 30px; border-radius: 16px; max-width: 700px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-family: inherit; }
        .checkbox-group { display: flex; gap: 20px; margin: 20px 0; flex-wrap: wrap; }
        
        /* Image Upload Styles */
        .image-upload-area {
            border: 2px dashed #ddd;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: #fafafa;
        }
        .image-upload-area:hover { border-color: #D4AF37; background: #fef9e6; }
        .upload-icon { font-size: 48px; color: #D4AF37; margin-bottom: 10px; }
        .upload-text { color: #666; font-size: 14px; }
        .upload-buttons { display: flex; gap: 10px; justify-content: center; margin-top: 15px; flex-wrap: wrap; }
        .upload-btn { padding: 8px 16px; background: #D4AF37; color: #000; border: none; border-radius: 8px; cursor: pointer; font-size: 12px; display: inline-flex; align-items: center; gap: 8px; }
        .upload-btn-secondary { background: #666; color: white; }
        .image-preview { margin-top: 15px; position: relative; display: inline-block; }
        .image-preview img { max-width: 150px; max-height: 150px; border-radius: 8px; border: 2px solid #D4AF37; padding: 3px; }
        .remove-image { position: absolute; top: -10px; right: -10px; background: #dc3545; color: white; border-radius: 50%; width: 25px; height: 25px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 12px; }
        .hidden-file-input { display: none; }
        .current-image { margin-bottom: 15px; }
        .current-image img { max-width: 100px; border-radius: 8px; }
        
        .btn-submit { background: #D4AF37; color: #000; padding: 12px 30px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .btn-cancel { background: #666; color: white; padding: 12px 30px; text-decoration: none; border-radius: 8px; margin-left: 10px; display: inline-block; }
        .alert-success { background: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .alert-error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        
        @media (max-width: 1024px) { 
            .sidebar { width: 80px; } 
            .sidebar-header h2, .sidebar-nav a span { display: none; } 
            .sidebar-nav a { justify-content: center; } 
            .main-content { margin-left: 80px; } 
        }
        @media (max-width: 768px) {
            .upload-buttons { flex-direction: column; }
            .upload-btn { justify-content: center; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header"><h2>Style Rwanda</h2></div>
        <nav class="sidebar-nav">
            <a href="index.php"><i class="fas fa-tachometer-alt"></i><span> Dashboard</span></a>
            <a href="orders.php"><i class="fas fa-shopping-cart"></i><span> Orders</span></a>
            <a href="products.php"><i class="fas fa-box"></i><span> Products</span></a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
        </nav>
    </div>
    
    <div class="main-content">
        <h1 style="margin-bottom: 20px;">Edit Product</h1>
        
        <?php if ($success): ?>
            <div class="alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="POST" id="productForm">
                <div class="form-group">
                    <label>Product Name *</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Description *</label>
                    <textarea name="description" rows="4" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Price (RWF) *</label>
                    <input type="number" name="price" step="1000" value="<?php echo $product['price']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category" required>
                        <option value="Men" <?php echo $product['category'] == 'Men' ? 'selected' : ''; ?>>Men</option>
                        <option value="Women" <?php echo $product['category'] == 'Women' ? 'selected' : ''; ?>>Women</option>
                        <option value="Footwear" <?php echo $product['category'] == 'Footwear' ? 'selected' : ''; ?>>Footwear</option>
                        <option value="Accessories" <?php echo $product['category'] == 'Accessories' ? 'selected' : ''; ?>>Accessories</option>
                    </select>
                </div>
                
                <!-- Image Upload Section -->
                <div class="form-group">
                    <label>Product Image</label>
                    
                    <?php if ($product['image_url']): ?>
                    <div class="current-image" id="currentImage">
                        <p>Current Image:</p>
                        <img src="<?php echo $product['image_url']; ?>" alt="Current product image">
                        <button type="button" onclick="removeCurrentImage()" style="margin-left: 10px; padding: 5px 10px; background: #dc3545; color: white; border: none; border-radius: 5px; cursor: pointer;">Remove</button>
                    </div>
                    <?php endif; ?>
                    
                    <div class="image-upload-area" id="uploadArea">
                        <div class="upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <div class="upload-text">
                            Drag & drop new image or click buttons below
                        </div>
                        <div class="upload-buttons">
                            <button type="button" class="upload-btn" id="uploadFromComputer">
                                <i class="fas fa-laptop"></i> From Computer
                            </button>
                            <button type="button" class="upload-btn upload-btn-secondary" id="uploadFromCamera">
                                <i class="fas fa-camera"></i> Take Photo
                            </button>
                        </div>
                        <div id="imagePreview" class="image-preview" style="display: none;"></div>
                    </div>
                    <input type="hidden" name="image_url" id="imageUrl" value="<?php echo htmlspecialchars($product['image_url']); ?>">
                    <input type="file" id="fileInput" accept="image/jpeg,image/png,image/webp,image/jpg" style="display: none;">
                    <input type="file" id="cameraInput" accept="image/jpeg,image/png,image/webp" capture="environment" style="display: none;">
                </div>
                
                <div class="form-group">
                    <label>Sizes (comma separated)</label>
                    <input type="text" name="sizes" value="<?php echo htmlspecialchars($sizes_list); ?>" placeholder="S,M,L,XL">
                </div>
                
                <div class="form-group">
                    <label>Colors (comma separated)</label>
                    <input type="text" name="colors" value="<?php echo htmlspecialchars($colors_list); ?>" placeholder="Black,White,Gold">
                </div>
                
                <div class="form-group">
                    <label>Stock Quantity *</label>
                    <input type="number" name="stock" value="<?php echo $product['stock']; ?>" required>
                </div>
                
                <div class="checkbox-group">
                    <label><input type="checkbox" name="is_featured" <?php echo $product['is_featured'] ? 'checked' : ''; ?>> Featured Product</label>
                    <label><input type="checkbox" name="is_new" <?php echo $product['is_new'] ? 'checked' : ''; ?>> New Arrival</label>
                </div>
                
                <div>
                    <button type="submit" class="btn-submit">Update Product</button>
                    <a href="products.php" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        const fileInput = document.getElementById('fileInput');
        const cameraInput = document.getElementById('cameraInput');
        const imagePreview = document.getElementById('imagePreview');
        const imageUrlInput = document.getElementById('imageUrl');
        
        document.getElementById('uploadFromComputer').addEventListener('click', () => fileInput.click());
        document.getElementById('uploadFromCamera').addEventListener('click', () => cameraInput.click());
        
        fileInput.addEventListener('change', (e) => { if (e.target.files.length > 0) uploadImage(e.target.files[0]); });
        cameraInput.addEventListener('change', (e) => { if (e.target.files.length > 0) uploadImage(e.target.files[0]); });
        
        async function uploadImage(file) {
            const formData = new FormData();
            formData.append('product_image', file);
            
            imagePreview.style.display = 'block';
            imagePreview.innerHTML = '<div style="text-align: center;"><i class="fas fa-spinner fa-spin"></i> Uploading...</div>';
            
            const response = await fetch('upload.php', { method: 'POST', body: formData });
            const result = await response.json();
            
            if (result.success) {
                imagePreview.innerHTML = `<div style="position: relative; display: inline-block;"><img src="${result.image_url}" alt="New image"><div class="remove-image" onclick="removeNewImage()"><i class="fas fa-times"></i></div></div>`;
                imageUrlInput.value = result.image_url;
                document.getElementById('currentImage')?.remove();
                showNotification('Image updated!', 'success');
            } else {
                imagePreview.innerHTML = `<div style="color: red;">Error: ${result.error}</div>`;
                showNotification(result.error, 'error');
            }
        }
        
        function removeNewImage() {
            imagePreview.style.display = 'none';
            imagePreview.innerHTML = '';
        }
        
        function removeCurrentImage() {
            document.getElementById('currentImage').remove();
            imageUrlInput.value = '';
            showNotification('Image removed', 'success');
        }
        
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.textContent = message;
            notification.style.cssText = `position:fixed;bottom:20px;right:20px;padding:12px20px;background:${type === 'success' ? '#28a745' : '#dc3545'};color:white;border-radius:8px;z-index:9999;`;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }
    </script>
</body>
</html>