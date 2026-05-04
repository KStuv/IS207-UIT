<div class="test-config">
	<div style="display: flex; justify-content: space-between; align-items: center;">
		<h3 style="margin-top: 0; color: #333; margin-bottom: 0;">Cấu Hình Đề Thi & Câu Hỏi</h3>
		<button type="button" id="importQuestionsBtn" class="btn btn-primary" style="padding: 8px 16px; font-size: 14px;">
			<i class="bx bx-file" style="font-size: 1.2rem; vertical-align: -0.125em; margin-right: 5px;"></i> Nhập câu hỏi</button>
	</div>
	<div class="config-row">
		<div class="config-group">
			<label>Đề Thi <span class="required">*</span></label>
			<select id="testSelect" onchange="onTestChange()" required>
				<option value="">-- Chọn đề thi --</option>
			</select>
		</div>
		<div class="config-group">
			<label>Phần (Part) <span class="required">*</span></label>
			<select id="partSelect" onchange="onPartChange()" required>
				<option value="">-- Chọn part --</option>
				<option value="1">Part 1: Ảnh</option>
				<option value="2">Part 2: Câu hỏi ngắn</option>
				<option value="3">Part 3: Hội thoại</option>
				<option value="4">Part 4: Độc thoại</option>
				<option value="5">Part 5: Đọc câu hoàn chỉnh</option>
				<option value="6">Part 6: Điền từ</option>
				<option value="7">Part 7: Đọc hiểu</option>
			</select>
		</div>
	</div>
</div>

<!-- Import Modal -->
<div id="importModal" class="modal" style="display: none;">
	<div class="modal-content">
		<div class="modal-header">
			<h2>Nhập Câu Hỏi từ File JSON</h2>
			<span class="close-modal" onclick="closeImportModal()">&times;</span>
		</div>
		<div class="modal-body">
			<div class="form-group">
				<label>Chọn Đề Thi <span class="required">*</span></label>
				<select id="importTestSelect" required>
					<option value="">-- Chọn đề thi --</option>
				</select>
			</div>
			<div class="form-group">
				<label>Chọn File JSON <span class="required">*</span></label>
				<input type="file" id="importFileInput" accept=".json" required>
				<small style="color: #666; display: block; margin-top: 5px;">Định dạng: JSON array chứa các câu hỏi</small>
			</div>
			<div id="importError" class="error-message" style="display: none;"></div>
			<div id="importSuccess" class="success-message" style="display: none;"></div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-secondary" onclick="closeImportModal()">Hủy</button>
			<button type="button" class="btn btn-primary" onclick="handleImportSubmit()">Nhập</button>
		</div>
	</div>
</div>

<div id="messageBox" class="message-box"></div>
<div id="partInfo" class="part-info"></div>
