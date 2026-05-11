/**
 * main.js - Điểm đầu vào của ứng dụng (Khởi tạo App)
 */

document.addEventListener('DOMContentLoaded', initApp);

function initApp() {
	setupUIFromParams();
	setupEventListeners();
	loadTestsData();
}

function setupUIFromParams() {
	const partSelect = document.getElementById('partSelect');
	if (partSelect) partSelect.disabled = true;

	console.log('ACTION_PARAM:', ACTION_PARAM, 'TEST_ID_PARAM:', TEST_ID_PARAM);

	if (ACTION_PARAM === 'edit' && TEST_ID_PARAM) {
		console.log('Mode: EDIT');
		toggleCreateTestForm(false);
		toggleOtherForms(true);
		
		// Đợi loadTests xong rồi trigger chọn test
		setTimeout(() => {
			const testSelect = document.getElementById('testSelect');
			if (testSelect && testSelect.querySelector(`option[value="${TEST_ID_PARAM}"]`)) {
				testSelect.value = TEST_ID_PARAM;
				onTestChange();
				setTimeout(() => {
					if (partSelect) partSelect.value = '1';
					onPartChange();
				}, 300);
			}
		}, 500);
	} else if (ACTION_PARAM === 'create') {
		// Khi nhấn "Tạo Bài Thi" từ adminTest: chỉ hiển thị form tạo, ẩn phần còn lại
		console.log('Mode: CREATE - Hiding other forms');
		toggleCreateTestForm(true);
		toggleOtherForms(false);
	} else {
		// Mặc định hiện tất cả
		console.log('Mode: DEFAULT - Showing all forms');
		toggleCreateTestForm(true);
		toggleOtherForms(true);
		addBlock('single');
	}
}

function setupEventListeners() {
	const createForm = document.getElementById('createTestForm');
	if (createForm) {
		createForm.addEventListener('submit', handleCreateTestSubmit);
	}
}

/**
 * Các hàm callback cần thiết cho sự kiện trong component
 */
function onTestChange() {
	const testSelect = document.getElementById('testSelect');
	const partSelect = document.getElementById('partSelect');
	if (!testSelect || !partSelect) return;

	const testId = testSelect.value;

	if (!testId) {
		showMessage('Vui lòng chọn đề thi', 'error');
		partSelect.value = '';
		const partInfo = document.getElementById('partInfo');
		if (partInfo) partInfo.classList.remove('show');
		return;
	}
	partSelect.disabled = false;
	partSelect.value = '1';
	onPartChange();
}

function onPartChange() {
	const partSelect = document.getElementById('partSelect');
	if (!partSelect) return;

	const part = partSelect.value;
	if (!part) {
		const partInfo = document.getElementById('partInfo');
		if (partInfo) partInfo.classList.remove('show');
		return;
	}

	const messageBox = document.getElementById('messageBox');
	if (messageBox) {
		messageBox.className = 'message-box';
		messageBox.textContent = '';
	}

	const config = PART_CONFIG[parseInt(part)];
	const partInfo = document.getElementById('partInfo');
	if (partInfo) {
		partInfo.innerHTML = `
			<strong>${config.name}</strong>
			Yêu cầu: ${config.requiresImage ? '✓ Hình ảnh' : ''} 
			${config.requiresAudio ? '✓ Âm thanh' : ''} 
			${config.requiresContent ? '✓ Nội dung' : ''}
		`;
		partInfo.classList.add('show');
	}

	document.querySelectorAll('.question-block').forEach(block => updateMediaBadges(block, part));
	loadSavedQuestionsToForm();
}
