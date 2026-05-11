/**
 * ui.js - Các hàm xử lý giao diện (Hiển thị thông báo, toggle form)
 */

function toggleCreateTestForm(show) {
	const formSection = document.querySelector('.form-section');
	if (formSection) formSection.style.display = show ? 'block' : 'none';
}

function toggleOtherForms(show) {
	const elements = ['.test-config', '#partInfo', '.header-actions', '#questions-container'];
	console.log('toggleOtherForms:', show, 'Elements:', elements);
	
	// Đặt attribute trên container để dễ debug
	const container = document.querySelector('.container-wrapper');
	if (container) {
		container.setAttribute('data-ui-mode', show ? 'full' : 'create-only');
	}
	
	if (show) {
		// Hiển thị
		elements.forEach(selector => {
			const el = document.querySelector(selector);
			if (el) {
				console.log('Showing:', selector);
				el.style.display = '';
				el.classList.remove('hidden');
			}
		});
	} else {
		// Ẩn
		elements.forEach(selector => {
			const el = document.querySelector(selector);
			if (el) {
				console.log('Hiding:', selector);
				el.style.display = 'none';
				el.classList.add('hidden');
			}
		});
	}
}

function showMessage(message, type) {
	const messageBox = document.getElementById('messageBox');
	if (!messageBox) return;

	messageBox.textContent = message;
	messageBox.className = `message-box ${type}`;
	messageBox.style.display = 'block';

	if (type === 'success') {
		setTimeout(() => { messageBox.className = 'message-box'; }, 5000);
	}
}

function updateMediaBadges(block, part) {
	const config = PART_CONFIG[parseInt(part)];
	if (!config) return;

	const updateEls = (selector, required, reqText, hintText) => {
		const labels = block.querySelectorAll(`${selector} .media-required-badge`);
		const hints = block.querySelectorAll(`${selector} .media-hint`);
		labels.forEach(l => l.textContent = required ? reqText : '');
		hints.forEach(h => h.textContent = hintText);
	};

	updateEls('.upload-item:nth-child(2)', config.requiresAudio, '(Bắt buộc)', config.requiresAudio ? 'MP3, WAV, OGG - tối đa 50MB' : 'Tùy chọn');
	updateEls('.upload-item:nth-child(1)', config.requiresImage, '(Bắt buộc)', config.requiresImage ? 'JPG, PNG, GIF - tối đa 5MB' : 'Tùy chọn');
}

function updateQuestionCount() {
	const singleQCount = document.querySelectorAll('.single-type').length;
	const subQCount = Array.from(document.querySelectorAll('.group-type')).reduce((sum, group) => {
		return sum + group.querySelectorAll('.sub-question-item').length;
	}, 0);

	const countElement = document.getElementById('questionCount');
	if (countElement) countElement.textContent = (singleQCount + subQCount).toString();
}

function previewMedia(input, type) {
	let container = input.nextElementSibling;
	while (container && !container.classList.contains('preview-container')) {
		container = container.nextElementSibling;
	}
	if (!container) {
		const uploadItem = input.closest('.upload-item');
		if (uploadItem) container = uploadItem.querySelector('.preview-container');
	}
	if (!container) return;

	container.innerHTML = '';
	if (!input.files || !input.files[0]) return;

	const file = input.files[0];
	const maxSize = type === 'audio' ? 50 * 1024 * 1024 : 5 * 1024 * 1024;
	if (file.size > maxSize) {
		showMessage(`File quá lớn! Tối đa ${type === 'audio' ? '50MB' : '5MB'}`, 'error');
		input.value = ''; return;
	}

	const url = URL.createObjectURL(file);
	const isImage = type === 'image' || (type === 'auto' && file.type.startsWith('image'));
	const isAudio = type === 'audio' || (type === 'auto' && file.type.startsWith('audio'));

	if (isImage) {
		container.innerHTML = `<img src="${url}" style="max-width: 200px;">`;
	} else if (isAudio) {
		container.innerHTML = `<audio controls src="${url}" style="width: 100%;"></audio>`;
	}
}
