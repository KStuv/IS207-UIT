<!DOCTYPE html>
<html lang="vi">
<head>
    <?php include('./components/metadata.php'); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Bài Thi</title>
    <link href="../styles/adminTestStyle.css" rel="stylesheet">
    <link rel="stylesheet" href="./components/componentsStyle.css">
</head>
<body>
    <?php include './components/navBar.php'; ?>
<main class="container">
<div class="create-test-section">
        <a href="./questions.php?action=create" class="create-test-button">Tạo Bài Thi</a>
    </div>

    <div class="list-section">
        <div class="list-header">
            <h2>Danh Sách Bài Thi</h2>
            <div class="table-toolbar">
                <div class="search-wrapper">
                    <input type="text" placeholder="Tìm kiếm theo tiêu đề...">
                </div>
                <div class="filter-wrapper">
                    <select><option>Tất cả phân loại</option><option>Premium</option><option>Thường</option></select>
                </div>
                <div class="filter-wrapper">
                    <select><option>Tất cả trạng thái</option><option>Hoạt động</option><option>Tạm ẩn</option></select>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Tiêu đề</th>
                    <th>Phân loại</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                </tr>
            </thead>
            <tbody id="testTableBody">
            </tbody>
        </table>
    </div>
</main>
<?php include './components/footer.php'; ?>

<div class="modal-overlay" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Chi Tiết & Chỉnh Sửa</h3>
            <button class="close-btn" id="closeModalBtn">&times;</button>
        </div>
        <form id="editForm">
            <input type="hidden" id="edit_id">
            
            <div class="form-group">
                <label for="edit_title">Tiêu đề</label>
                <input type="text" id="edit_title" required>
            </div>
            
            <div class="checkbox-group-wrapper">
                <div class="checkbox-group">
                    <input type="checkbox" id="edit_premium">
                    <label for="edit_premium">Premium</label>
                </div>
                <div class="checkbox-group">
                    <input type="checkbox" id="edit_active">
                    <label for="edit_active">Hoạt động</label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-danger" id="btnDelete">Xóa Bài Thi</button>
                <button type="submit" class="btn-submit">Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>

<script src="../js/adminTest.js"></script>
</body>
</html>