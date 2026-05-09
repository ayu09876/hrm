(function () {
    const decoder = new TextDecoder('utf-8');
    const expectedHeaders = ['nik', 'full name', 'time in', 'time out'];
    const builtInFormats = {
        14: 'm/d/yyyy',
        15: 'd-mmm-yy',
        16: 'd-mmm',
        17: 'mmm-yy',
        18: 'h:mm AM/PM',
        19: 'h:mm:ss AM/PM',
        20: 'h:mm',
        21: 'h:mm:ss',
        22: 'm/d/yyyy h:mm',
        45: 'mm:ss',
        46: '[h]:mm:ss',
        47: 'mmss.0'
    };

    function byId(id) {
        return document.getElementById(id);
    }

    function normalizeHeader(value) {
        return String(value || '')
            .trim()
            .toLowerCase()
            .replace(/\s+/g, ' ');
    }

    function showFeedback(container, message) {
        if (!container) {
            return;
        }

        container.innerHTML = '<i class="bi bi-exclamation-circle-fill"></i> ' + message;
        container.style.display = 'flex';
    }

    function clearFeedback(container) {
        if (!container) {
            return;
        }

        container.textContent = '';
        container.style.display = 'none';
    }

    function updatePickedFile(fileInput, fileName, previewButton) {
        const file = fileInput.files && fileInput.files[0];

        if (!file) {
            fileName.textContent = '';
            fileName.style.display = 'none';
            previewButton.disabled = true;
            return;
        }

        fileName.textContent = file.name;
        fileName.style.display = 'block';
        previewButton.disabled = false;
    }

    function columnLettersToIndex(reference) {
        const letters = String(reference || '').replace(/[0-9]/g, '').toUpperCase();
        let index = 0;

        for (let i = 0; i < letters.length; i += 1) {
            index = (index * 26) + (letters.charCodeAt(i) - 64);
        }

        return Math.max(index - 1, 0);
    }

    function pad(number) {
        return String(number).padStart(2, '0');
    }

    function formatDate(date) {
        return [
            date.getUTCFullYear(),
            pad(date.getUTCMonth() + 1),
            pad(date.getUTCDate())
        ].join('-');
    }

    function formatTime(date) {
        return [
            pad(date.getUTCHours()),
            pad(date.getUTCMinutes())
        ].join(':');
    }

    function excelSerialToDate(serial) {
        const wholeDays = Math.floor(serial);
        const fraction = serial - wholeDays;
        const milliseconds = Math.round(fraction * 86400000);

        return new Date(Date.UTC(1899, 11, 30 + wholeDays, 0, 0, 0, milliseconds));
    }

    function describeFormat(formatCode, numFmtId) {
        const builtIn = builtInFormats[numFmtId] || '';
        const format = String(formatCode || builtIn)
            .toLowerCase()
            .replace(/"[^"]*"/g, '')
            .replace(/\[[^\]]*]/g, '');
        const hasDate = /y|d/.test(format);
        const hasTime = /h|s|am\/pm/.test(format);

        return {
            hasDate,
            hasTime,
            isDateLike: hasDate || hasTime || Object.prototype.hasOwnProperty.call(builtInFormats, numFmtId)
        };
    }

    function parseXml(xmlText, label) {
        const documentNode = new DOMParser().parseFromString(xmlText, 'application/xml');

        if (documentNode.getElementsByTagName('parsererror').length > 0) {
            throw new Error('Could not read ' + label + ' from the Excel file.');
        }

        return documentNode;
    }

    function parseSharedStrings(xmlText) {
        if (!xmlText) {
            return [];
        }

        const documentNode = parseXml(xmlText, 'sharedStrings.xml');

        return Array.from(documentNode.getElementsByTagNameNS('*', 'si')).map(function (node) {
            return Array.from(node.getElementsByTagNameNS('*', 't'))
                .map(function (textNode) {
                    return textNode.textContent || '';
                })
                .join('');
        });
    }

    function parseStyles(xmlText) {
        if (!xmlText) {
            return [];
        }

        const documentNode = parseXml(xmlText, 'styles.xml');
        const customFormats = new Map();
        const numFmtNodes = Array.from(documentNode.getElementsByTagNameNS('*', 'numFmt'));

        numFmtNodes.forEach(function (node) {
            customFormats.set(
                Number(node.getAttribute('numFmtId')),
                node.getAttribute('formatCode') || ''
            );
        });

        const cellXfs = documentNode.getElementsByTagNameNS('*', 'cellXfs')[0];

        if (!cellXfs) {
            return [];
        }

        return Array.from(cellXfs.children).map(function (node) {
            const numFmtId = Number(node.getAttribute('numFmtId') || 0);

            return describeFormat(customFormats.get(numFmtId), numFmtId);
        });
    }

    function formatExcelNumeric(value, style) {
        if (!style || !style.isDateLike || !/^[-+]?\d+(\.\d+)?$/.test(value)) {
            return String(value || '').trim();
        }

        const date = excelSerialToDate(Number(value));

        if (style.hasDate && style.hasTime) {
            return formatDate(date) + ' ' + formatTime(date);
        }

        if (style.hasDate) {
            return formatDate(date);
        }

        return formatTime(date);
    }

    function parseSheetRows(xmlText, sharedStrings, styles) {
        const documentNode = parseXml(xmlText, 'worksheet data');
        const rows = [];

        Array.from(documentNode.getElementsByTagNameNS('*', 'row')).forEach(function (rowNode) {
            const row = [];

            Array.from(rowNode.getElementsByTagNameNS('*', 'c')).forEach(function (cellNode) {
                const reference = cellNode.getAttribute('r') || '';
                const columnIndex = columnLettersToIndex(reference);
                const cellType = cellNode.getAttribute('t') || '';
                const styleIndex = Number(cellNode.getAttribute('s') || 0);
                const style = styles[styleIndex] || null;
                let value = '';

                if (cellType === 'inlineStr') {
                    value = Array.from(cellNode.getElementsByTagNameNS('*', 't'))
                        .map(function (textNode) {
                            return textNode.textContent || '';
                        })
                        .join('');
                } else {
                    const valueNode = cellNode.getElementsByTagNameNS('*', 'v')[0];
                    value = valueNode ? (valueNode.textContent || '') : '';
                }

                if (cellType === 's') {
                    value = sharedStrings[Number(value)] || '';
                } else if (cellType === 'b') {
                    value = value === '1' ? 'TRUE' : 'FALSE';
                } else {
                    value = formatExcelNumeric(value, style);
                }

                row[columnIndex] = String(value || '').trim();
            });

            rows.push(row);
        });

        return rows;
    }

    function findEndOfCentralDirectory(bytes) {
        for (let offset = bytes.length - 22; offset >= 0; offset -= 1) {
            if (
                bytes[offset] === 0x50 &&
                bytes[offset + 1] === 0x4b &&
                bytes[offset + 2] === 0x05 &&
                bytes[offset + 3] === 0x06
            ) {
                return offset;
            }
        }

        throw new Error('The selected file is not a valid .xlsx archive.');
    }

    async function inflate(bytes) {
        if (typeof DecompressionStream === 'undefined') {
            throw new Error('This browser cannot preview .xlsx files yet. Please use a Chromium-based browser.');
        }

        const stream = new Blob([bytes]).stream().pipeThrough(new DecompressionStream('deflate-raw'));
        const buffer = await new Response(stream).arrayBuffer();

        return new Uint8Array(buffer);
    }

    async function readZipEntries(arrayBuffer) {
        const bytes = new Uint8Array(arrayBuffer);
        const view = new DataView(arrayBuffer);
        const endOfCentralDirectory = findEndOfCentralDirectory(bytes);
        const entryCount = view.getUint16(endOfCentralDirectory + 10, true);
        let centralDirectoryOffset = view.getUint32(endOfCentralDirectory + 16, true);
        const entries = new Map();

        for (let index = 0; index < entryCount; index += 1) {
            if (view.getUint32(centralDirectoryOffset, true) !== 0x02014b50) {
                throw new Error('The selected file has an invalid central directory.');
            }

            const compressionMethod = view.getUint16(centralDirectoryOffset + 10, true);
            const compressedSize = view.getUint32(centralDirectoryOffset + 20, true);
            const fileNameLength = view.getUint16(centralDirectoryOffset + 28, true);
            const extraFieldLength = view.getUint16(centralDirectoryOffset + 30, true);
            const fileCommentLength = view.getUint16(centralDirectoryOffset + 32, true);
            const localHeaderOffset = view.getUint32(centralDirectoryOffset + 42, true);
            const fileNameStart = centralDirectoryOffset + 46;
            const fileNameEnd = fileNameStart + fileNameLength;
            const fileName = decoder.decode(bytes.slice(fileNameStart, fileNameEnd));

            entries.set(fileName, {
                compressionMethod,
                compressedSize,
                localHeaderOffset
            });

            centralDirectoryOffset = fileNameEnd + extraFieldLength + fileCommentLength;
        }

        async function getText(name) {
            const entry = entries.get(name);

            if (!entry) {
                return null;
            }

            if (view.getUint32(entry.localHeaderOffset, true) !== 0x04034b50) {
                throw new Error('The selected file contains an invalid local file header.');
            }

            const fileNameLength = view.getUint16(entry.localHeaderOffset + 26, true);
            const extraFieldLength = view.getUint16(entry.localHeaderOffset + 28, true);
            const dataStart = entry.localHeaderOffset + 30 + fileNameLength + extraFieldLength;
            const compressed = bytes.slice(dataStart, dataStart + entry.compressedSize);

            if (entry.compressionMethod === 0) {
                return decoder.decode(compressed);
            }

            if (entry.compressionMethod === 8) {
                return decoder.decode(await inflate(compressed));
            }

            throw new Error('The selected file uses an unsupported Excel compression method.');
        }

        return {
            getText: getText,
            list: function () {
                return Array.from(entries.keys());
            }
        };
    }

    function resolveWorksheetPath(workbookXml, relationshipsXml, entryNames) {
        if (!workbookXml || !relationshipsXml) {
            const firstSheet = entryNames.find(function (name) {
                return /^xl\/worksheets\/.+\.xml$/.test(name);
            });

            if (!firstSheet) {
                throw new Error('No worksheet could be found in the selected Excel file.');
            }

            return firstSheet;
        }

        const workbookDocument = parseXml(workbookXml, 'workbook.xml');
        const relationshipsDocument = parseXml(relationshipsXml, 'workbook.xml.rels');
        const firstSheet = workbookDocument.getElementsByTagNameNS('*', 'sheet')[0];

        if (!firstSheet) {
            throw new Error('The selected Excel file does not contain a worksheet.');
        }

        const relationshipId = firstSheet.getAttribute('r:id');
        const relationship = Array.from(relationshipsDocument.getElementsByTagNameNS('*', 'Relationship')).find(function (node) {
            return node.getAttribute('Id') === relationshipId;
        });

        if (!relationship) {
            throw new Error('The worksheet relationship could not be resolved.');
        }

        return 'xl/' + String(relationship.getAttribute('Target') || '').replace(/^\/+/, '');
    }

    function buildAttendanceRows(rows) {
        if (!rows.length) {
            throw new Error('The selected Excel file is empty.');
        }

        const headerRow = rows[0].map(normalizeHeader);
        const columnIndexes = expectedHeaders.map(function (header) {
            return headerRow.indexOf(header);
        });

        if (columnIndexes.includes(-1)) {
            throw new Error('Expected columns: NIK, Full Name, Time In, and Time Out.');
        }

        const dataRows = rows.slice(1)
            .map(function (row) {
                return {
                    nik: String(row[columnIndexes[0]] || '').trim(),
                    full_name: String(row[columnIndexes[1]] || '').trim(),
                    time_in: String(row[columnIndexes[2]] || '').trim(),
                    time_out: String(row[columnIndexes[3]] || '').trim()
                };
            })
            .filter(function (row) {
                return row.nik || row.full_name || row.time_in || row.time_out;
            });

        if (!dataRows.length) {
            throw new Error('No attendance rows were found below the header.');
        }

        return dataRows;
    }

    async function parseAttendanceWorkbook(file) {
        const zip = await readZipEntries(await file.arrayBuffer());
        const workbookXml = await zip.getText('xl/workbook.xml');
        const relationshipsXml = await zip.getText('xl/_rels/workbook.xml.rels');
        const sharedStringsXml = await zip.getText('xl/sharedStrings.xml');
        const stylesXml = await zip.getText('xl/styles.xml');
        const worksheetPath = resolveWorksheetPath(workbookXml, relationshipsXml, zip.list());
        const worksheetXml = await zip.getText(worksheetPath);

        if (!worksheetXml) {
            throw new Error('The first worksheet could not be read from the selected Excel file.');
        }

        return buildAttendanceRows(
            parseSheetRows(
                worksheetXml,
                parseSharedStrings(sharedStringsXml),
                parseStyles(stylesXml)
            )
        );
    }

    window.initAttendanceImport = function initAttendanceImport(options) {
        const modalElement = byId(options.modalId);
        const form = byId(options.formId);
        const fileInput = byId(options.fileInputId);
        const dropZone = byId(options.dropZoneId);
        const fileName = byId(options.fileNameId);
        const previewButton = byId(options.previewBtnId);
        const payloadField = byId(options.payloadFieldId);
        const feedback = byId(options.feedbackId);

        if (!modalElement || !form || !fileInput || !dropZone || !fileName || !previewButton || !payloadField) {
            return;
        }

        if (options.autoOpen) {
            new bootstrap.Modal(modalElement).show();
        }

        fileInput.addEventListener('change', function () {
            clearFeedback(feedback);
            payloadField.value = '';
            updatePickedFile(fileInput, fileName, previewButton);
        });

        ['dragenter', 'dragover'].forEach(function (eventName) {
            dropZone.addEventListener(eventName, function (event) {
                event.preventDefault();
                dropZone.classList.add('over');
            });
        });

        ['dragleave', 'drop'].forEach(function (eventName) {
            dropZone.addEventListener(eventName, function (event) {
                event.preventDefault();
                dropZone.classList.remove('over');
            });
        });

        dropZone.addEventListener('drop', function (event) {
            const file = event.dataTransfer.files[0];

            if (!file || !/\.xlsx$/i.test(file.name)) {
                showFeedback(feedback, 'Please choose a .xlsx file.');
                return;
            }

            const transfer = new DataTransfer();
            transfer.items.add(file);
            fileInput.files = transfer.files;
            clearFeedback(feedback);
            payloadField.value = '';
            updatePickedFile(fileInput, fileName, previewButton);
        });

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            clearFeedback(feedback);

            const file = fileInput.files && fileInput.files[0];

            if (!file) {
                showFeedback(feedback, 'Choose an Excel file before previewing.');
                return;
            }

            if (!/\.xlsx$/i.test(file.name)) {
                showFeedback(feedback, 'Only .xlsx files are supported.');
                return;
            }

            const originalLabel = previewButton.innerHTML;

            previewButton.disabled = true;
            previewButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Reading Excel';

            try {
                payloadField.value = JSON.stringify(await parseAttendanceWorkbook(file));
                form.submit();
            } catch (error) {
                payloadField.value = '';
                previewButton.disabled = false;
                previewButton.innerHTML = originalLabel;
                showFeedback(feedback, error.message || 'The selected Excel file could not be previewed.');
            }
        });
    };
})();
