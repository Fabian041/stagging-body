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
        // Global variables to track order
        let originalSequenceData = [];
        let currentOrder = [];

        // Add CSS styles
        document.head.insertAdjacentHTML('beforeend', `
            <style>
                .sequence-changed {
                    background-color: #fff3cd !important;
                    border-left: 3px solid #ffc107;
                    position: relative;
                }
                .swap-info {
                    position: absolute;
                    left: 10px;
                    top: 50%;
                    transform: translateY(-50%);
                    font-size: 0.8em;
                    background: #28a745;
                    color: white;
                    padding: 2px 6px;
                    border-radius: 10px;
                    z-index: 1;
                }
                .sequence-input-container {
                    position: relative;
                    margin-left: 60px;
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
                #resetChangesBtn {
                    margin-left: 10px;
                }
                .item-row {
                    position: relative;
                }
            </style>
        `);

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('loadItemsBtn').addEventListener('click', loadProductionItems);
            document.getElementById('saveOrderBtn').addEventListener('click', saveProductionSequence);

            // Add Reset Changes button
            const saveBtnContainer = document.querySelector('#saveOrderBtn').parentNode;
            saveBtnContainer.insertAdjacentHTML('beforeend',
                '<button id="resetChangesBtn" class="btn btn-outline-danger" style="display:none;">Reset Changes</button>'
            );

            document.getElementById('resetChangesBtn').addEventListener('click', resetChanges);
        });

        function loadProductionItems() {
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

                    // Store original data and order
                    originalSequenceData = data.map(item => ({
                        id: item.id,
                        back_no: item.back_no,
                        customer: item.customer,
                        order_qty: item.order_qty,
                        delivery_time: item.delivery_time,
                        sequence: item.sequence
                    }));

                    // Sort by original sequence
                    originalSequenceData.sort((a, b) => a.sequence - b.sequence);
                    currentOrder = originalSequenceData.map(item => item.id);

                    // Create items in original order
                    originalSequenceData.forEach((item, index) => {
                        const itemElement = document.createElement('div');
                        itemElement.className = 'item-row';
                        itemElement.setAttribute('data-id', item.id);
                        itemElement.setAttribute('data-original-seq', item.sequence);
                        itemElement.setAttribute('data-current-seq', index + 1);

                        itemElement.innerHTML = `
                            <div class="col-md-2">
                                <div class="sequence-input-container">
                                    <input type="number" 
                                           class="industrial-sequence-input" 
                                           value="${index + 1}" 
                                           min="1" 
                                           max="${originalSequenceData.length}"
                                           onchange="handleSequenceChange(this)">
                                </div>
                            </div>
                            <div class="col-md-3 font-weight-bold">
                                ${item.back_no}
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
                    document.getElementById('resetChangesBtn').style.display = 'none';
                })
                .catch(error => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-search me-2"></i> LOAD PRODUCTION';
                    alert('Error loading production data: ' + (error.message || 'Unknown error'));
                });
        }

        function resetChanges() {
            const container = document.getElementById('itemsContainer');
            container.innerHTML = ''; // Clear current items

            // Recreate items in original order
            originalSequenceData
                .sort((a, b) => a.sequence - b.sequence)
                .forEach((item, index) => {
                    const itemElement = document.createElement('div');
                    itemElement.className = 'item-row';
                    itemElement.setAttribute('data-id', item.id);
                    itemElement.setAttribute('data-original-seq', item.sequence);
                    itemElement.setAttribute('data-current-seq', index + 1);

                    itemElement.innerHTML = `
                        <div class="col-md-2">
                            <div class="sequence-input-container">
                                <input type="number" 
                                       class="industrial-sequence-input" 
                                       value="${index + 1}" 
                                       min="1" 
                                       max="${originalSequenceData.length}"
                                       onchange="handleSequenceChange(this)">
                            </div>
                        </div>
                        <div class="col-md-3 font-weight-bold">
                            ${item.back_no}
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

            // Reset tracking
            currentOrder = originalSequenceData.map(item => item.id);
            document.getElementById('resetChangesBtn').style.display = 'none';
        }

        function handleSequenceChange(changedInput) {
            const container = document.getElementById('itemsContainer');
            const allRows = Array.from(container.querySelectorAll('.item-row'));

            // Get the row that was changed
            const changedRow = changedInput.closest('.item-row');
            const changedRowId = changedRow.getAttribute('data-id');
            const newPosition = parseInt(changedInput.value);
            const oldPosition = parseInt(changedRow.getAttribute('data-current-seq'));

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

            // Show reset button
            document.getElementById('resetChangesBtn').style.display = 'inline-block';

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
            const targetOldPosition = parseInt(targetRow.getAttribute('data-current-seq'));

            // Remove any existing swap info
            const existingSwapInfo = changedRow.querySelector('.swap-info');
            if (existingSwapInfo) changedRow.removeChild(existingSwapInfo);
            const existingTargetSwapInfo = targetRow.querySelector('.swap-info');
            if (existingTargetSwapInfo) targetRow.removeChild(existingTargetSwapInfo);

            // Create and add new swap info
            const swapInfo = document.createElement('div');
            swapInfo.className = 'swap-info';
            swapInfo.textContent = `${oldPosition} ↔ ${newPosition}`;
            changedRow.insertBefore(swapInfo, changedRow.firstChild);

            const targetSwapInfo = document.createElement('div');
            targetSwapInfo.className = 'swap-info';
            targetSwapInfo.textContent = `${targetOldPosition} ↔ ${oldPosition}`;
            targetRow.insertBefore(targetSwapInfo, targetRow.firstChild);

            // Highlight both rows
            changedRow.classList.add('sequence-changed');
            targetRow.classList.add('sequence-changed');

            // Swap the DOM positions
            container.insertBefore(targetRow, changedRow);
            container.insertBefore(changedRow, targetRow.nextSibling);

            // Update sequence numbers
            const updatedRows = Array.from(container.querySelectorAll('.item-row'));
            updatedRows.forEach((row, index) => {
                const input = row.querySelector('.industrial-sequence-input');
                input.value = index + 1;
                row.setAttribute('data-current-seq', index + 1);
            });

            // Update current order tracking
            const changedIndex = currentOrder.indexOf(changedRowId);
            const targetIndex = currentOrder.indexOf(targetRowId);
            currentOrder[changedIndex] = targetRowId;
            currentOrder[targetIndex] = changedRowId;
        }

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

                    // Update original sequence data to match current order
                    originalSequenceData.sort((a, b) => {
                        return currentOrder.indexOf(a.id) - currentOrder.indexOf(b.id);
                    });

                    // Update original sequence numbers
                    originalSequenceData.forEach((item, index) => {
                        item.sequence = index + 1;
                    });

                    document.getElementById('resetChangesBtn').style.display = 'none';
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
