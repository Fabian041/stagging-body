@extends('layouts.root.main')

@section('main')
    <style>
        .industrial-container {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
        }

        .order-input {
            width: 60px;
            text-align: center;
            font-weight: bold;
            border: 2px solid #6c757d;
            background-color: #fff;
        }

        .item-row {
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-left: 4px solid #007bff;
            margin-bottom: 8px;
            padding: 12px 15px;
            display: flex;
            align-items: center;
        }

        .sequence-input-container {
            position: relative;
            width: 100%;
            max-width: 100px;
        }

        .industrial-sequence-input {
            width: 100%;
            height: 60px;
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
            border: 3px solid #343a40;
            background-color: #e9ecef;
            border-radius: 5px;
            padding: 5px;
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.1);
            -moz-appearance: textfield;
        }

        .industrial-sequence-input::-webkit-outer-spin-button,
        .industrial-sequence-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .industrial-sequence-input:focus {
            outline: none;
            border-color: #007bff;
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.5);
        }

        .item-row:hover {
            background-color: #f8f9fa;
            border-left-color: #0056b3;
        }

        .item-badge {
            font-size: 0.85rem;
            padding: 5px 10px;
            background-color: #6c757d;
            color: white;
        }

        .industrial-header {
            background-color: #343a40;
            color: white;
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .industrial-btn {
            border-radius: 4px;
            font-weight: bold;
            padding: 8px 20px;
        }

        .info-panel {
            background-color: #e7f1ff;
            border-left: 4px solid #007bff;
            padding: 10px 15px;
            margin-bottom: 15px;
        }
    </style>
    <div class="row mt-3">
        <div class="col-12">
            <div class="industrial-container">
                <div class="industrial-header">
                    <h4 class="mb-0"><i class="fas fa-cogs me-2"></i> PRODUCTION SEQUENCE MANAGEMENT</h4>
                </div>

                <form id="reorderForm">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="reorderDate" class="form-label font-weight-bold">PRODUCTION DATE</label>
                            <input type="date" id="reorderDate" name="date" class="form-control"
                                value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="reorderLine" class="form-label font-weight-bold">PRODUCTION LINE</label>
                            <select id="reorderLine" name="line" class="form-control">
                                <option value="AS003">LINE AS003</option>
                                <option value="AS004">LINE AS004</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="button" id="loadItemsBtn" class="btn btn-primary industrial-btn">
                                <i class="fas fa-search me-2"></i> LOAD PRODUCTION
                            </button>
                        </div>
                    </div>
                </form>

                <div id="reorderContainer" class="mt-3 d-none">
                    <div class="info-panel">
                        <i class="fas fa-info-circle me-2"></i> Edit the sequence numbers to change production order.
                        <strong>LOWER NUMBERS = HIGHER PRIORITY</strong>
                    </div>

                    <div class="row mb-2 font-weight-bold">
                        <div class="col-md-1">SEQ #</div>
                        <div class="col-md-3">BACK NUMBER</div>
                        <div class="col-md-4">CUSTOMER</div>
                        <div class="col-md-2 text-center">QUANTITY</div>
                        <div class="col-md-2 text-center">DEADLINE</div>
                    </div>

                    <div id="itemsContainer" class="mb-4">
                        <!-- Items will be loaded here -->
                    </div>

                    <div class="text-right">
                        <button id="saveOrderBtn" class="btn btn-success industrial-btn">
                            <i class="fas fa-save me-2"></i> SAVE PRODUCTION SEQUENCE
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Add CSS for the enhanced highlight effect
        document.head.insertAdjacentHTML('beforeend', `
            <style>
                .sequence-changed {
                    background-color: #fff3cd !important;
                    border-left: 3px solid #ffc107;
                    position: relative;
                    animation: pulse 2s infinite;
                }
                @keyframes pulse {
                    0% { background-color: #fff3cd; }
                    50% { background-color: #ffe8a1; }
                    100% { background-color: #fff3cd; }
                }
                .sequence-changed::after {
                    content: attr(data-swap-info);
                    position: absolute;
                    right: 10px;
                    top: 50%;
                    transform: translateY(-50%);
                    font-size: 0.8em;
                    color: #28a745;
                    font-weight: bold;
                    padding: 2px 8px;
                    background: white;
                    border-radius: 10px;
                    border: 1px solid #28a745;
                }
                .sequence-input-container {
                    position: relative;
                }
                .sequence-input-container::after {
                    content: '↕';
                    position: absolute;
                    right: 10px;
                    top: 50%;
                    transform: translateY(-50%);
                    color: #666;
                    pointer-events: none;
                }
                #resetHighlightsBtn {
                    margin-left: 10px;
                }
                .swap-info-badge {
                    display: inline-block;
                    margin-left: 5px;
                    font-size: 0.7em;
                    background: #17a2b8;
                    color: white;
                    padding: 1px 5px;
                    border-radius: 3px;
                }
            </style>
        `);

        // Track all changed rows with their swap info
        let changedRows = new Map();

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('loadItemsBtn').addEventListener('click', loadProductionItems);
            document.getElementById('saveOrderBtn').addEventListener('click', saveProductionSequence);

            // Add reset highlights button
            const saveBtnContainer = document.querySelector('#saveOrderBtn').parentNode;
            saveBtnContainer.insertAdjacentHTML('beforeend',
                '<button id="resetHighlightsBtn" class="btn btn-outline-secondary">Reset Highlights</button>');

            document.getElementById('resetHighlightsBtn').addEventListener('click', function() {
                document.querySelectorAll('.sequence-changed').forEach(el => {
                    el.classList.remove('sequence-changed');
                    el.removeAttribute('data-swap-info');
                });
                changedRows.clear();
            });
        });

        function loadProductionItems() {
            // Clear highlights when loading new items
            changedRows.clear();
            document.querySelectorAll('.sequence-changed').forEach(el => {
                el.classList.remove('sequence-changed');
                el.removeAttribute('data-swap-info');
            });

            // Rest of your existing loadProductionItems function...
            const date = document.getElementById('reorderDate').value;
            const line = document.getElementById('reorderLine').value;

            if (!date) {
                alert('Please select a production date');
                return;
            }

            const btn = document.getElementById('loadItemsBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> LOADING...';

            fetch('/api/production-items?date=' + date + '&line=' + line)
                .then(response => response.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-search me-2"></i> LOAD PRODUCTION';

                    if (data.length === 0) {
                        alert('No production items found for selected criteria');
                        return;
                    }

                    const container = document.getElementById('itemsContainer');
                    container.innerHTML = '';

                    data.sort((a, b) => a.sequence - b.sequence);

                    data.forEach((item, index) => {
                        const itemElement = document.createElement('div');
                        itemElement.className = 'item-row';
                        itemElement.setAttribute('data-id', item.id);
                        itemElement.setAttribute('data-sequence', index + 1);

                        // Add highlight if this row was previously changed
                        if (changedRows.has(item.id.toString())) {
                            itemElement.classList.add('sequence-changed');
                            itemElement.setAttribute('data-swap-info', changedRows.get(item.id.toString()));
                        }

                        itemElement.innerHTML = `
                            <div class="col-md-2">
                                <div class="sequence-input-container">
                                    <input type="number" 
                                           class="industrial-sequence-input" 
                                           value="${index + 1}" 
                                           min="1" 
                                           max="${data.length}"
                                           onchange="handleSequenceChange(this)">
                                </div>
                            </div>
                            <div class="col-md-3 font-weight-bold">
                                ${item.back_no}
                                ${changedRows.has(item.id.toString()) ? 
                                 `<span class="swap-info-badge">Modified</span>` : ''}
                            </div>
                            <div class="col-md-3">
                                ${item.customer}
                            </div>
                            <div class="col-md-2 text-center">
                                <span class="badge item-badge">${item.order_qty} UNITS</span>
                            </div>
                            <div class="col-md-2 text-right font-weight-bold">
                                ${item.delivery_time}
                            </div>
                        `;

                        container.appendChild(itemElement);
                    });

                    document.getElementById('reorderContainer').classList.remove('d-none');
                })
                .catch(error => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-search me-2"></i> LOAD PRODUCTION';
                    alert('Error loading production data: ' + (error.message || 'Unknown error'));
                });
        }

        function handleSequenceChange(changedInput) {
            const container = document.getElementById('itemsContainer');
            const allRows = Array.from(container.querySelectorAll('.item-row'));

            // Get the row that was changed
            const changedRow = changedInput.closest('.item-row');
            const changedRowId = changedRow.getAttribute('data-id');
            const newPosition = parseInt(changedInput.value);
            const oldPosition = parseInt(changedRow.getAttribute('data-sequence'));

            // Validate input
            if (isNaN(newPosition)) {
                changedInput.value = oldPosition;
                return;
            }

            if (newPosition < 1) {
                changedInput.value = 1;
                return;
            }

            if (newPosition > allRows.length) {
                changedInput.value = allRows.length;
                return;
            }

            // If position didn't change, do nothing
            if (newPosition === oldPosition) {
                return;
            }

            // Find the row that currently has the new position value
            const targetRow = allRows.find(row => {
                const input = row.querySelector('.industrial-sequence-input');
                return row !== changedRow && parseInt(input.value) === newPosition;
            });

            if (!targetRow) {
                changedInput.value = oldPosition;
                return;
            }

            const targetRowId = targetRow.getAttribute('data-id');
            const targetOldPosition = parseInt(targetRow.getAttribute('data-sequence'));

            // Create swap info text
            const swapInfo = `Swapped ${oldPosition} ↔ ${newPosition}`;
            const targetSwapInfo = `Swapped ${targetOldPosition} ↔ ${oldPosition}`;

            // Store swap info for both rows
            changedRows.set(changedRowId, swapInfo);
            changedRows.set(targetRowId, targetSwapInfo);

            // Highlight both rows with swap info
            changedRow.classList.add('sequence-changed');
            changedRow.setAttribute('data-swap-info', swapInfo);

            targetRow.classList.add('sequence-changed');
            targetRow.setAttribute('data-swap-info', targetSwapInfo);

            // Swap the DOM positions
            const changedRowClone = changedRow.cloneNode(true);
            const targetRowClone = targetRow.cloneNode(true);

            container.replaceChild(targetRowClone, changedRow);
            container.replaceChild(changedRowClone, targetRow);

            // Update the sequence numbers for both swapped rows
            const changedRowInput = changedRowClone.querySelector('.industrial-sequence-input');
            const targetRowInput = targetRowClone.querySelector('.industrial-sequence-input');

            changedRowInput.value = newPosition;
            changedRowClone.setAttribute('data-sequence', newPosition);

            targetRowInput.value = oldPosition;
            targetRowClone.setAttribute('data-sequence', oldPosition);

            // Update all other sequence numbers to be sequential
            const updatedRows = Array.from(container.querySelectorAll('.item-row'));
            updatedRows.forEach((row, index) => {
                // Skip the two rows we already updated
                if (row !== changedRowClone && row !== targetRowClone) {
                    const input = row.querySelector('.industrial-sequence-input');
                    input.value = index + 1;
                    row.setAttribute('data-sequence', index + 1);
                }
            });

            // Re-sort all rows based on the new sequence numbers
            const sortedRows = updatedRows.sort((a, b) => {
                const aSeq = parseInt(a.getAttribute('data-sequence'));
                const bSeq = parseInt(b.getAttribute('data-sequence'));
                return aSeq - bSeq;
            });

            // Re-append all rows in the correct order
            sortedRows.forEach(row => container.appendChild(row));
        }

        // Your existing saveProductionSequence function...
        function saveProductionSequence() {
            const date = document.getElementById('reorderDate').value;
            const line = document.getElementById('reorderLine').value;
            const itemRows = document.querySelectorAll('#itemsContainer .item-row');

            if (!date || !line) {
                alert('Please select both date and production line');
                return;
            }

            if (itemRows.length === 0) {
                alert('No production items to sequence');
                return;
            }

            const newOrder = Array.from(itemRows).map(row => ({
                id: row.getAttribute('data-id')
            }));

            const btn = document.getElementById('saveOrderBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> SAVING...';

            fetch('/pulling/settings/reorder', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    body: JSON.stringify({
                        date: date,
                        line: line,
                        new_order: newOrder
                    })
                })
                .then(async response => {
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        const text = await response.text();
                        throw new Error(text || 'Server returned non-JSON response');
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message || 'Unknown server error');
                    }

                    // Add save confirmation to highlights
                    const now = new Date();
                    const timeString = now.toLocaleTimeString();
                    document.querySelectorAll('.sequence-changed').forEach(row => {
                        const currentInfo = row.getAttribute('data-swap-info') || '';
                        row.setAttribute('data-swap-info', `${currentInfo} (Saved ${timeString})`);
                    });

                    alert('Production sequence updated successfully');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating sequence: ' + (error.message || 'Check console for details'));
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-save me-2"></i> SAVE PRODUCTION SEQUENCE';
                });
        }
    </script>
@endpush
