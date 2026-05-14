/**
 * adminTest.js - Quản lý danh sách bài thi
 * Chức năng: Load, render, filter, edit, delete tests
 */

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
    tests.forEach((test, idx) => {
        // Xử lý is_premium: có thể là boolean hoặc số
        const isPremiumValue = test.is_premium === true || test.is_premium === 1 || test.is_premium === '1';
        // Xử lý is_active: có thể là boolean hoặc số
        const isActiveValue = test.is_active === true || test.is_active === 1 || test.is_active === '1';
        
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
        
        // Lọc theo phân loại - xử lý cả boolean và số
        let categoryMatch = true;
        const isPremium = test.is_premium === true || test.is_premium === 1 || test.is_premium === '1';
        if (categoryValue === 'Premium') {
            categoryMatch = isPremium;
        } else if (categoryValue === 'Thường') {
            categoryMatch = !isPremium;
        }
        
        // Lọc theo trạng thái - xử lý cả boolean và số
        let statusMatch = true;
        const isActive = test.is_active === true || test.is_active === 1 || test.is_active === '1';
        if (statusValue === 'Hoạt động') {
            statusMatch = isActive;
        } else if (statusValue === 'Tạm ẩn') {
            statusMatch = !isActive;
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

// ====== KHỞI TẠO KHI BẮT ĐẦU TRANG ======
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
