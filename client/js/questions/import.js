/**
 * import.js - Xử lý tính năng import câu hỏi từ file JSON
 */

// Event listener cho nút import
document.addEventListener('DOMContentLoaded', function() {
	const importBtn = document.getElementById('importQuestionsBtn');
	if (importBtn) {
		importBtn.addEventListener('click', openImportModal);
	}
});

/**
 * Mở modal import
 */
function openImportModal() {
	const modal = document.getElementById('importModal');
	const testSelect = document.getElementById('importTestSelect');
	const mainTestSelect = document.getElementById('testSelect');
	
	// Copy danh sách test từ dropdown chính
	testSelect.innerHTML = mainTestSelect.innerHTML;
	testSelect.value = mainTestSelect.value || '';
	
	// Reset form
	document.getElementById('importFileInput').value = '';
	document.getElementById('importError').style.display = 'none';
	document.getElementById('importSuccess').style.display = 'none';
	
	modal.style.display = 'flex';
}

/**
 * Đóng modal import
 */
function closeImportModal() {
	const modal = document.getElementById('importModal');
	modal.style.display = 'none';
}

/**
 * Xử lý submit form import
 */
function handleImportSubmit() {
	const testId = document.getElementById('importTestSelect').value;
	const fileInput = document.getElementById('importFileInput');
	const errorDiv = document.getElementById('importError');
	const successDiv = document.getElementById('importSuccess');
	
	// Reset messages
	errorDiv.style.display = 'none';
	successDiv.style.display = 'none';
	
	// Validate
	if (!testId) {
		showError('Vui lòng chọn đề thi', errorDiv);
		return;
	}
	
	if (!fileInput.files.length) {
		showError('Vui lòng chọn file JSON', errorDiv);
		return;
	}
	
	const file = fileInput.files[0];
	if (!file.name.endsWith('.json')) {
		showError('File phải có định dạng .json', errorDiv);
		return;
	}
	
	// Đọc file
	const reader = new FileReader();
	reader.onload = function(e) {
		try {
			// Parse JSON và gửi lên server
			const jsonData = JSON.parse(e.target.result);
			importQuestionsToServer(testId, jsonData, successDiv, errorDiv);
		} catch (err) {
			showError('Lỗi parse JSON: ' + err.message, errorDiv);
		}
	};
	
	reader.onerror = function() {
		showError('Lỗi đọc file', errorDiv);
	};
	
	reader.readAsText(file);
}

/**
 * Gửi dữ liệu import lên server
 */
function importQuestionsToServer(testId, jsonData, successDiv, errorDiv) {
	// Tạo FormData để gửi dữ liệu
	const formData = new FormData();
	formData.append('test_id', testId);
	formData.append('questions_data', JSON.stringify(jsonData));
	
	// Gửi POST request đến API import
	fetch('/api/questions/import', {
		method: 'POST',
		body: formData
	})
	.then(response => response.json())
	.then(data => {
		if (data.success) {
			showSuccess(`Nhập thành công ${data.imported_count} câu hỏi`, successDiv);
			setTimeout(() => {
				closeImportModal();
			}, 1500);
		} else {
			showError(data.message || 'Lỗi khi import', errorDiv);
		}
	})
	.catch(error => {
		showError('Lỗi kết nối: ' + error.message, errorDiv);
	});
}

/**
 * Hiển thị lỗi
 */
function showError(message, element) {
	element.textContent = message;
	element.style.display = 'block';
}

/**
 * Hiển thị thành công
 */
function showSuccess(message, element) {
	element.textContent = message;
	element.style.display = 'block';
}

// Đóng modal khi click ngoài
window.addEventListener('click', function(event) {
	const modal = document.getElementById('importModal');
	if (event.target === modal) {
		closeImportModal();
	}
});
