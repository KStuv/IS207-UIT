<template id="single-question-template-4options">
	<div class="question-block single-type" data-type="single">
		<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
			<div class="badge single block-title">Câu hỏi đơn</div>
			<button class="btn-remove" onclick="removeBlock(this)">Xóa</button>
		</div>

		<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
			<div>
				<label style="font-weight: 600; display: block; margin-bottom: 5px;">Số thứ tự câu hỏi</label>
				<input type="number" class="question-number form-control" min="1" max="200">
			</div>
			<div style="visibility: hidden;">
				<label style="font-weight: 600; display: block; margin-bottom: 5px;">Placeholder</label>
			</div>
		</div>
		<div class="media-upload-section">
			<div class="upload-item">
				<label><i class="bx bx-camera-alt" style="font-size: 1.2rem; vertical-align: -0.125em;"></i> Hình ảnh <span class="media-required-badge" style="color: red;"></span></label>
				<input type="file" accept="image/*" class="image-file" onchange="previewMedia(this, 'image')">
				<small class="media-hint" style="color: #666;">Tùy chọn</small>
				<div class="preview-container"></div>
			</div>
			<div class="upload-item">
				<label><i class="bx bx-volume-full" style="font-size: 1.2rem; vertical-align: -0.125em;"></i> Âm thanh <span class="media-required-badge" style="color: red;"></span></label>
				<input type="file" accept="audio/*" class="audio-file" onchange="previewMedia(this, 'audio')">
				<small class="media-hint" style="color: #666;">Tùy chọn</small>
				<div class="preview-container"></div>
			</div>
		</div>

		<label><strong>Nội dung câu hỏi:</strong></label>
		<textarea class="form-control question-content" placeholder="Nhập câu hỏi..." onpaste="handleAutoFillPaste(event)"></textarea>

		<div class="options-container">
			<label style="font-weight: 600; display: block; margin-bottom: 10px;">Đáp án <span style="color: red;">*</span></label>
			<div class="option-item"><input type="radio" class="correct-radio" value="A"><span>A.</span><input type="text" class="form-control option-content" placeholder="Đáp án A" required></div>
			<div class="option-item"><input type="radio" class="correct-radio" value="B"><span>B.</span><input type="text" class="form-control option-content" placeholder="Đáp án B" required></div>
			<div class="option-item"><input type="radio" class="correct-radio" value="C"><span>C.</span><input type="text" class="form-control option-content" placeholder="Đáp án C" required></div>
			<div class="option-item"><input type="radio" class="correct-radio" value="D"><span>D.</span><input type="text" class="form-control option-content" placeholder="Đáp án D" required></div>
			<small style="color: #666; display: block; margin-top: 8px;">Chọn đáp án đúng</small>
		</div>

		<label style="font-weight: 600; display: block; margin-top: 15px; margin-bottom: 5px;">Giải thích (Tùy chọn)</label>
		<textarea class="form-control explanation" placeholder="Giải thích đáp án..." rows="2"></textarea>
	</div>
</template>

<template id="single-question-template-3options">
	<div class="question-block single-type" data-type="single">
		<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
			<div class="badge single block-title">Câu hỏi đơn</div>
			<button class="btn-remove" onclick="removeBlock(this)">Xóa</button>
		</div>

		<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
			<div>
				<label style="font-weight: 600; display: block; margin-bottom: 5px;">Số thứ tự câu hỏi</label>
				<input type="number" class="question-number form-control" min="1" max="200">
			</div>
			<div style="visibility: hidden;">
				<label style="font-weight: 600; display: block; margin-bottom: 5px;">Placeholder</label>
			</div>
		</div>
		<div class="media-upload-section">
			<div class="upload-item">
				<label><i class="bx bx-camera-alt" style="font-size: 1.2rem; vertical-align: -0.125em;"></i> Hình ảnh <span class="media-required-badge" style="color: red;"></span></label>
				<input type="file" accept="image/*" class="image-file" onchange="previewMedia(this, 'image')">
				<small class="media-hint" style="color: #666;">Tùy chọn</small>
				<div class="preview-container"></div>
			</div>
			<div class="upload-item">
				<label><i class="bx bx-volume-full" style="font-size: 1.2rem; vertical-align: -0.125em;"></i> Âm thanh <span class="media-required-badge" style="color: red;"></span></label>
				<input type="file" accept="audio/*" class="audio-file" onchange="previewMedia(this, 'audio')">
				<small class="media-hint" style="color: #666;">Tùy chọn</small>
				<div class="preview-container"></div>
			</div>
		</div>

		<label><strong>Nội dung câu hỏi:</strong></label>
		<textarea class="form-control question-content" placeholder="Nhập câu hỏi..." onpaste="handleAutoFillPaste(event)"></textarea>

		<div class="options-container">
			<label style="font-weight: 600; display: block; margin-bottom: 10px;">Đáp án <span style="color: red;">*</span></label>
			<div class="option-item"><input type="radio" class="correct-radio" value="A"><span>A.</span><input type="text" class="form-control option-content" placeholder="Đáp án A" required></div>
			<div class="option-item"><input type="radio" class="correct-radio" value="B"><span>B.</span><input type="text" class="form-control option-content" placeholder="Đáp án B" required></div>
			<div class="option-item"><input type="radio" class="correct-radio" value="C"><span>C.</span><input type="text" class="form-control option-content" placeholder="Đáp án C" required></div>
			<small style="color: #666; display: block; margin-top: 8px;">Chọn đáp án đúng</small>
		</div>

		<label style="font-weight: 600; display: block; margin-top: 15px; margin-bottom: 5px;">Giải thích (Tùy chọn)</label>
		<textarea class="form-control explanation" placeholder="Giải thích đáp án..." rows="2"></textarea>
	</div>
</template>

<template id="group-question-template-4options">
	<div class="question-block group-type" data-type="group">
		<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
			<div class="badge group block-title">Cụm câu hỏi</div>
			<button class="btn-remove" onclick="removeBlock(this)">Xóa</button>
		</div>

		<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px;">
			<div class="upload-item">
				<label> <i class="bx bx-camera-alt" style="font-size: 1.2rem; vertical-align: -0.125em;"></i> Hình ảnh <span class="media-required-badge" style="color: red;"></span></label>
				<input type="file" accept="image/*" class="group-image-file" onchange="previewMedia(this, 'image')">
				<small class="media-hint" style="color: #666;">Tùy chọn</small>
				<div class="preview-container"></div>
			</div>
			<div class="upload-item">
				<label> <i class="bx bx-volume-full" style="font-size: 1.2rem; vertical-align: -0.125em;"></i> Âm thanh <span class="media-required-badge" style="color: red;"></span></label>
				<input type="file" accept="audio/*" class="group-audio-file" onchange="previewMedia(this, 'audio')">
				<small class="media-hint" style="color: #666;">Tùy chọn</small>
				<div class="preview-container"></div>
			</div>
			<div class="upload-item">
				<label> <i class="bx bx-file" style="font-size: 1.2rem; vertical-align: -0.125em;"></i> Đoạn văn (Passages)</label>
				<textarea class="form-control passage-content" placeholder="Dán đoạn văn dùng chung vào đây..." style="height: 120px;"></textarea>
			</div>
		</div>

		<div class="sub-questions-container"></div>

		<button class="btn-add-sub" onclick="addSubQuestionBtn(this)">+ Thêm 1 câu hỏi vào cụm</button>
	</div>
</template>

<template id="group-question-template-3options">
	<div class="question-block group-type" data-type="group">
		<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
			<div class="badge group block-title">Cụm câu hỏi</div>
			<button class="btn-remove" onclick="removeBlock(this)">Xóa</button>
		</div>

		<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px;">
			<div class="upload-item">
				<label> <i class="bx bx-camera-alt" style="font-size: 1.2rem; vertical-align: -0.125em;"></i> Hình ảnh <span class="media-required-badge" style="color: red;"></span></label>
				<input type="file" accept="image/*" class="group-image-file" onchange="previewMedia(this, 'image')">
				<small class="media-hint" style="color: #666;">Tùy chọn</small>
				<div class="preview-container"></div>
			</div>
			<div class="upload-item">
				<label> <i class="bx bx-volume-full" style="font-size: 1.2rem; vertical-align: -0.125em;"></i> Âm thanh <span class="media-required-badge" style="color: red;"></span></label>
				<input type="file" accept="audio/*" class="group-audio-file" onchange="previewMedia(this, 'audio')">
				<small class="media-hint" style="color: #666;">Tùy chọn</small>
				<div class="preview-container"></div>
			</div>
			<div class="upload-item">
				<label> <i class="bx bx-file" style="font-size: 1.2rem; vertical-align: -0.125em;"></i> Đoạn văn (Passages)</label>
				<textarea class="form-control passage-content" placeholder="Dán đoạn văn dùng chung vào đây..." style="height: 120px;"></textarea>
			</div>
		</div>

		<div class="sub-questions-container"></div>

		<button class="btn-add-sub" onclick="addSubQuestionBtn(this)">+ Thêm 1 câu hỏi vào cụm</button>
	</div>
</template>

<template id="sub-question-template-4options">
	<div class="sub-question-item">
		<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
			<button class="btn-remove-sub" onclick="removeSubQuestion(this)">Xóa</button>
		</div>
		<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
			<div>
				<label style="font-weight: 600; display: block; margin-bottom: 5px;">Số thứ tự câu hỏi</label>
				<input type="number" class="sub-question-number form-control" min="1" max="200" value="1">
			</div>
			<div style="visibility: hidden;">
				<label style="font-weight: 600; display: block; margin-bottom: 5px;">Placeholder</label>
			</div>
		</div>
		<div style="margin-bottom: 10px;">
			<label style="font-weight: 600; display: block; margin-bottom: 5px;">Nội dung câu hỏi</label>
			<textarea class="form-control question-content" placeholder="Nhập câu hỏi..." rows="2" onpaste="handleAutoFillPaste(event)"></textarea>
		</div>
		<div style="margin-top: 10px; margin-bottom: 5px; font-weight: 600;">Đáp án <span style="color: red;">*</span></div>
		<div class="sub-options-grid">
			<div class="sub-option"><input type="radio" class="correct-radio" value="A" required><span>A.</span><input type="text" class="form-control option-content" placeholder="Đáp án A" required></div>
			<div class="sub-option"><input type="radio" class="correct-radio" value="B" required><span>B.</span><input type="text" class="form-control option-content" placeholder="Đáp án B" required></div>
			<div class="sub-option"><input type="radio" class="correct-radio" value="C" required><span>C.</span><input type="text" class="form-control option-content" placeholder="Đáp án C" required></div>
			<div class="sub-option"><input type="radio" class="correct-radio" value="D" required><span>D.</span><input type="text" class="form-control option-content" placeholder="Đáp án D" required></div>
		</div>
		<label style="font-weight: 600; display: block; margin-top: 15px; margin-bottom: 5px;">Giải thích (Tùy chọn)</label>
		<textarea class="form-control explanation" placeholder="Giải thích đáp án..." rows="2"></textarea>
	</div>
</template>

<template id="sub-question-template-3options">
	<div class="sub-question-item">
		<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
			<button class="btn-remove-sub" onclick="removeSubQuestion(this)">Xóa</button>
		</div>
		<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
			<div>
				<label style="font-weight: 600; display: block; margin-bottom: 5px;">Số thứ tự câu hỏi</label>
				<input type="number" class="sub-question-number form-control" min="1" max="200" value="1">
			</div>
			<div style="visibility: hidden;">
				<label style="font-weight: 600; display: block; margin-bottom: 5px;">Placeholder</label>
			</div>
		</div>
		<div style="margin-bottom: 10px;">
			<label style="font-weight: 600; display: block; margin-bottom: 5px;">Nội dung câu hỏi</label>
			<textarea class="form-control question-content" placeholder="Nhập câu hỏi..." rows="2" onpaste="handleAutoFillPaste(event)"></textarea>
		</div>
		<div style="margin-top: 10px; margin-bottom: 5px; font-weight: 600;">Đáp án <span style="color: red;">*</span></div>
		<div class="sub-options-grid">
			<div class="sub-option"><input type="radio" class="correct-radio" value="A" required><span>A.</span><input type="text" class="form-control option-content" placeholder="Đáp án A" required></div>
			<div class="sub-option"><input type="radio" class="correct-radio" value="B" required><span>B.</span><input type="text" class="form-control option-content" placeholder="Đáp án B" required></div>
			<div class="sub-option"><input type="radio" class="correct-radio" value="C" required><span>C.</span><input type="text" class="form-control option-content" placeholder="Đáp án C" required></div>
		</div>
		<label style="font-weight: 600; display: block; margin-top: 15px; margin-bottom: 5px;">Giải thích (Tùy chọn)</label>
		<textarea class="form-control explanation" placeholder="Giải thích đáp án..." rows="2"></textarea>
	</div>
</template>
