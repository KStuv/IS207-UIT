<?php include './components/metadata.php'; ?>
<?php include './components/navBar.php'; ?>
<?php include './components/header.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Bài Thi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../styles/adminTestStyle.css" rel="stylesheet">
    <link rel="stylesheet" href="./components/componentsStyle.css">
</head>
<body>

<div class="container">
    <div style="margin-top: 25px; margin-bottom: 20px; display: flex; justify-content: flex-end;">
        <a href="./questions.php" class="btn-submit" style="text-decoration: none; display: inline-block; background-color: var(--primary-color); color: white; border: none; padding: 10px 24px; font-size: 14px; font-weight: 600; border-radius: 6px; cursor: pointer; transition: 0.2s;">Tạo Bài Thi</a>
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
</div>

<div class="modal-overlay" id="editModal">
    <div class="modal-content" style="background: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-radius: 8px; padding: 25px; width: 450px;">
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ddd; padding-bottom: 15px; margin-bottom: 20px;">
            <h3 style="margin: 0; font-size: 18px;">Chi Tiết & Chỉnh Sửa</h3>
            <button class="close-btn" id="closeModalBtn" style="font-size: 24px; cursor: pointer; border: none; background: none; color: #999;">&times;</button>
        </div>
        <form id="editForm">
            <input type="hidden" id="edit_id">
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px;">Tiêu đề</label>
                <input type="text" id="edit_title" required style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; box-sizing: border-box;">
            </div>
            
            <div class="checkbox-group-wrapper" style="margin-top: 15px; margin-bottom: 20px; display: flex; gap: 20px;">
                <div class="checkbox-group" style="display: flex; align-items: center;">
                    <input type="checkbox" id="edit_premium" style="width: 18px; height: 18px; margin-right: 8px; cursor: pointer;">
                    <label for="edit_premium" style="cursor: pointer; font-weight: normal; margin: 0;">Premium</label>
                </div>
                <div class="checkbox-group" style="display: flex; align-items: center;">
                    <input type="checkbox" id="edit_active" style="width: 18px; height: 18px; margin-right: 8px; cursor: pointer;">
                    <label for="edit_active" style="cursor: pointer; font-weight: normal; margin: 0;">Hoạt động</label>
                </div>
            </div>

            <div class="modal-footer" style="display: flex; justify-content: space-between; margin-top: 25px; padding-top: 15px; border-top: 1px dashed #ddd;">
                <button type="button" class="btn-danger" id="btnDelete" style="background-color: #EF4444; color: white; border: none; padding: 10px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px;">Xóa Bài Thi</button>
                <button type="submit" class="btn-submit" style="background-color: #4F46E5; color: white; border: none; padding: 10px 24px; font-size: 14px; font-weight: 600; border-radius: 6px; cursor: pointer;">Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>

    <script>

    // ====== BIẾN TOÀN CỤC ======
    /** Lưu trữ toàn bộ dữ liệu bài thi từ API để hỗ trợ filter */
    let allTests = [];

    // ====== HÀM TIỆN ÍCH ======
    /** Định dạng ngày tháng từ định dạng ISO sang định dạng Việt (DD/MM/YYYY) */
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    }

    // ====== RENDER BẢNG ======
    /**Render bảng danh sách bài thi từ dữ liệu được truyền vào
     * Mỗi hàng bao gồm: tiêu đề, phân loại badge, trạng thái badge, ngày tạo
     */
    function renderTestsTable(tests) {
        const tbody = document.getElementById('testTableBody');
        tbody.innerHTML = '';
        
        // Hiển thị thông báo nếu không có dữ liệu
        if (tests.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; color: #999;">Không tìm thấy bài thi nào</td></tr>';
            return;
        }
        
        // Duyệt qua từng bài thi và render hàng
        tests.forEach(test => {
            // Chuyển đổi giá trị số thành boolean (1 = true, 0 = false)
            const isPremiumValue = parseInt(test.is_premium) === 1;
            const isActiveValue = parseInt(test.is_active) === 1;
            
            // Chuẩn bị text và CSS class cho badge
            const isPremiumText = isPremiumValue ? 'Premium' : 'Thường';
            const premiumBadgeClass = isPremiumValue ? 'premium' : 'standard';
            const isActiveText = isActiveValue ? 'Hoạt động' : 'Tạm ẩn';
            const activeBadgeClass = isActiveValue ? 'active' : 'inactive';
            const createdDate = formatDate(test.created_at);
            
            // Tạo phần tử <tr> mới
            const row = document.createElement('tr');
            row.dataset.id = test.uuid;
            row.dataset.premium = isPremiumValue ? '1' : '0';
            row.dataset.active = isActiveValue ? '1' : '0';
            
            // Thêm nội dung HTML cho hàng
            row.innerHTML = `
                <td class="td-title"><strong>${test.title}</strong></td>
                <td><span class="badge ${premiumBadgeClass}">${isPremiumText}</span></td>
                <td><span class="badge ${activeBadgeClass}">${isActiveText}</span></td>
                <td>${createdDate}</td>
            `;
            
            // Gán event listener cho chuột phải (context menu)
            row.addEventListener('contextmenu', handleRowContextMenu);
            
            // Gán event listener cho double-click để chuyển đến trang chỉnh sửa câu hỏi
            row.addEventListener('dblclick', function() {
                const testId = this.getAttribute('data-id');
                window.location.href = `./questions.php?test_id=${testId}&action=edit`;
            });
            
            tbody.appendChild(row);
        });
    }

    // ====== LOAD DỮ LIỆU TỪ API ======
    /**
     * Tải danh sách toàn bộ bài thi từ API
     * Lưu dữ liệu vào biến allTests
     * Gọi renderTestsTable() để hiển thị bảng
     */
    async function loadTestsList() {
        try {
            const response = await fetch('/api/tests');
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            const result = await response.json();
            
            if (result.success && result.data && Array.isArray(result.data)) {
                allTests = result.data;
                renderTestsTable(allTests);
            }
        } catch (error) {
            console.error('Error loading tests:', error);
        }
    }

    // ====== FILTER DỮ LIỆU ======
    /**
     * Lọc dữ liệu bài thi dựa trên các tiêu chí:
     * - Tìm kiếm theo tiêu đề
     * - Lọc theo phân loại (Premium/Thường)
     * - Lọc theo trạng thái (Hoạt động/Tạm ẩn)
     */
    function filterTests() {
        const searchInput = document.querySelector('.search-wrapper input');
        const categoryFilter = document.querySelectorAll('.filter-wrapper select')[0];
        const statusFilter = document.querySelectorAll('.filter-wrapper select')[1];
        
        const searchText = searchInput.value.toLowerCase().trim();
        const categoryValue = categoryFilter.value;
        const statusValue = statusFilter.value;
        
        // Lọc dữ liệu allTests
        let filtered = allTests.filter(test => {
            // Tìm kiếm theo tiêu đề (không phân biệt hoa/thường)
            const titleMatch = test.title.toLowerCase().includes(searchText);
            
            // Lọc theo phân loại
            let categoryMatch = true;
            if (categoryValue === 'Premium') {
                categoryMatch = parseInt(test.is_premium) === 1;
            } else if (categoryValue === 'Thường') {
                categoryMatch = parseInt(test.is_premium) === 0;
            }
            
            // Lọc theo trạng thái
            let statusMatch = true;
            if (statusValue === 'Hoạt động') {
                statusMatch = parseInt(test.is_active) === 1;
            } else if (statusValue === 'Tạm ẩn') {
                statusMatch = parseInt(test.is_active) === 0;
            }
            
            return titleMatch && categoryMatch && statusMatch;
        });
        
        // Render bảng với dữ liệu đã filter
        renderTestsTable(filtered);
    }

    // ====== XỬ LÝ CLICK CHUỘT PHẢI ======
    /**
     * Xử lý sự kiện click chuột phải trên hàng bảng
     * Hiển thị modal chỉnh sửa với dữ liệu của bài thi được chọn
     * @param {event} e - Event object từ contextmenu
     */
    function handleRowContextMenu(e) {
        e.preventDefault();
        
        // Lấy dữ liệu từ hàng được click
        const modal = document.getElementById('editModal');
        const row = e.currentTarget;
        const testId = row.getAttribute('data-id');
        const isPremium = row.getAttribute('data-premium') === '1';
        const isActive = row.getAttribute('data-active') === '1';
        const titleElement = row.querySelector('.td-title strong');
        
        if (!titleElement) {
            console.error('Không tìm thấy tiêu đề bài thi');
            return;
        }
        
        const title = titleElement.innerText;
        
        // Điền dữ liệu vào các input của modal
        document.getElementById('edit_id').value = testId;
        document.getElementById('edit_title').value = title;
        document.getElementById('edit_premium').checked = isPremium;
        document.getElementById('edit_active').checked = isActive;
        
        // Hiển thị modal
        modal.classList.add('show');
    }

    // ====== KHỞI TẠO KHIBẤT ĐẦU TRANG ======
    /**
     * Khởi tạo các event listener và tải dữ liệu khi trang được load xong
     */
    document.addEventListener("DOMContentLoaded", function () {
        // Lấy các phần tử DOM cần thiết
        const modal = document.getElementById("editModal");
        const closeModalBtn = document.getElementById("closeModalBtn");
        const editForm = document.getElementById("editForm");
        const btnDelete = document.getElementById("btnDelete");

        // Tải danh sách bài thi từ database
        loadTestsList();

        // Gán event listener cho search input - gõ để tìm kiếm real-time
        const searchInput = document.querySelector('.search-wrapper input');
        searchInput.addEventListener('input', filterTests);

        // Gán event listener cho các dropdown filter
        const filterSelects = document.querySelectorAll('.filter-wrapper select');
        filterSelects.forEach(select => {
            select.addEventListener('change', filterTests);
        });

        // ====== ĐÓNG MODAL ======
        // Tắt modal khi nhấn nút X hoặc click ra ngoài vùng modal
        const closeModal = () => modal.classList.remove("show");
        closeModalBtn.addEventListener("click", closeModal);
        modal.addEventListener("click", function (e) {
            if (e.target === modal) closeModal();
        });

        // ====== XÓA BÀI THI ======
        /**
         * Xử lý khi nhấn nút "Xóa Bài Thi"
         * Gửi DELETE request đến API
         * Nếu thành công: reload bảng và đóng modal
         * Nếu thất bại: hiển thị thông báo lỗi
         */
        btnDelete.addEventListener("click", async function () {
            const id = document.getElementById("edit_id").value;
            if (confirm("Bạn có chắc chắn muốn xóa bài thi này không? Dữ liệu không thể khôi phục.")) {
                try {
                    const response = await fetch(`/api/tests/${id}`, {
                        method: 'DELETE'
                    });
                    const result = await response.json();
                    if (result.success) {
                        alert("Đã xóa bài thi thành công!");
                        loadTestsList();
                        filterTests();
                        closeModal();
                    } else {
                        alert("Lỗi xóa bài thi: " + (result.message || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error deleting test:', error);
                    alert("Lỗi xóa bài thi");
                }
            }
        });

        // ====== LƯU/CẬP NHẬT BÀI THI ======
        /**
         * Xử lý khi gửi form chỉnh sửa bài thi
         * Lấy dữ liệu từ các field input trong modal
         * Gửi PUT request đến API để cập nhật bài thi
         * Nếu thành công: reload bảng, reset filter, đóng modal
         * Nếu thất bại: hiển thị thông báo lỗi cho người dùng
         */
        editForm.addEventListener("submit", async function (e) {
            e.preventDefault();
            
            // Lấy dữ liệu từ form modal
            const id = document.getElementById("edit_id").value;
            const title = document.getElementById("edit_title").value;
            const isPremium = document.getElementById("edit_premium").checked ? 1 : 0;
            const isActive = document.getElementById("edit_active").checked ? 1 : 0;
            
            try {
                // Gửi PUT request để cập nhật bài thi
                const response = await fetch(`/api/tests/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        title,
                        is_premium: isPremium,
                        is_active: isActive
                    })
                });
                const result = await response.json();
                
                // Xử lý kết quả từ server
                if (result.success) {
                    alert("Đã lưu thay đổi thành công!");
                    loadTestsList();      // Tải lại danh sách từ database
                    filterTests();         // Áp dụng filter lại
                    closeModal();          // Đóng modal
                } else {
                    alert("Lỗi lưu thay đổi: " + (result.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error updating test:', error);
                alert("Lỗi lưu thay đổi");
            }
        });
    });
</script><?php include './components/footer.php'; ?>
</body>
</html>