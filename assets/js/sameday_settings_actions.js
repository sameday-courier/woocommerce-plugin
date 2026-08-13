document.addEventListener('DOMContentLoaded', function () {
    const actions = document.querySelector('.sameday-settings-actions');
    const submitRow = document.querySelector('#mainform p.submit');

    if (actions && submitRow) {
        submitRow.insertAdjacentElement('afterend', actions);
    }

    const overlay = document.getElementById('sameday-all-import-overlay');
    if (overlay && overlay.parentElement !== document.body) {
        document.body.appendChild(overlay);
    }

    const importButton = document.getElementById('sameday-all-import-button');
    if (!importButton || !overlay) {
        return;
    }

    const config = window.samedayAllImport || {};
    const statusNode = overlay.querySelector('[data-sameday-all-import-status]');
    const progressNode = overlay.querySelector('[data-sameday-all-import-progress]');
    let isRunning = false;

    importButton.addEventListener('click', function (event) {
        event.preventDefault();
        startImport();
    });

    function i18n(key, fallback) {
        return (config.i18n && config.i18n[key]) ? config.i18n[key] : fallback;
    }

    function getStepIds() {
        return Object.keys(config.steps || {})
            .map(function (key) {
                return parseInt(key, 10);
            })
            .filter(function (id) {
                return id > 0;
            })
            .sort(function (left, right) {
                return left - right;
            });
    }

    function getStepLabel(itemId) {
        const steps = config.steps || {};

        return steps[itemId] || steps[String(itemId)] || '';
    }

    function formatProcessing(itemId, processed, total) {
        const template = i18n('processing', 'Importing %s (%s/%s)');
        const current = Math.min((processed || 0) + 1, total || 0);

        return template
            .replace('%s', getStepLabel(itemId) || '')
            .replace('%s', String(current))
            .replace('%s', String(total || 0));
    }

    function setBusy(busy) {
        isRunning = busy;
        importButton.disabled = busy;
        importButton.setAttribute('aria-busy', busy ? 'true' : 'false');
        document.body.classList.toggle('sameday-all-import-busy', busy);

        if (busy) {
            overlay.hidden = false;
            overlay.setAttribute('aria-busy', 'true');
            return;
        }

        overlay.hidden = true;
        overlay.removeAttribute('aria-busy');
    }

    function updateProgress(processed, total, itemId) {
        const safeTotal = total || 0;
        const safeProcessed = processed || 0;
        const percent = safeTotal > 0 ? Math.round((safeProcessed / safeTotal) * 100) : 0;

        if (statusNode) {
            statusNode.textContent = formatProcessing(itemId, safeProcessed, safeTotal);
        }

        if (progressNode) {
            progressNode.style.width = percent + '%';
        }
    }

    function post(action, nonce, extra) {
        const payload = Object.assign({
            action: action,
            _wpnonce: nonce
        }, extra || {});
        const body = new URLSearchParams(payload);

        return fetch(config.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: body.toString()
        }).then(function (response) {
            return response.json().then(function (json) {
                return {
                    httpOk: response.ok,
                    success: !!(json && json.success),
                    data: json && json.data !== undefined ? json.data : {}
                };
            }, function () {
                return {
                    httpOk: response.ok,
                    success: false,
                    data: {}
                };
            });
        });
    }

    function extractErrorMessage(data) {
        if (typeof data === 'string' && data !== '') {
            return data;
        }

        if (data && typeof data.message === 'string' && data.message !== '') {
            return data.message;
        }

        return i18n('genericError', 'Something went wrong.');
    }

    function showNotice(type, message, failedSteps) {
        const wrap = document.querySelector('.wrap');
        if (!wrap) {
            return;
        }

        const previous = wrap.querySelectorAll('.sameday-all-import-notice');
        previous.forEach(function (node) {
            node.remove();
        });

        const notice = document.createElement('div');
        notice.className = 'notice notice-' + type + ' is-dismissible sameday-all-import-notice';

        const paragraph = document.createElement('p');
        paragraph.textContent = message;
        notice.appendChild(paragraph);

        (failedSteps || []).forEach(function (failedStep) {
            const detail = document.createElement('p');
            const label = document.createElement('strong');
            label.textContent = (failedStep.label || '') + ':';
            detail.appendChild(label);
            detail.appendChild(document.createTextNode(' ' + (failedStep.message || '')));
            notice.appendChild(detail);
        });

        const heading = wrap.querySelector('h1, h2');
        if (heading) {
            heading.insertAdjacentElement('afterend', notice);
        } else {
            wrap.insertAdjacentElement('afterbegin', notice);
        }

        notice.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function finish(data) {
        setBusy(false);

        const items = Array.isArray(data.items) ? data.items : [];
        const failedSteps = items.filter(function (item) {
            return item && item.status === 'error';
        });

        if (failedSteps.length === 0) {
            showNotice('success', i18n('complete', 'Process complete, all data is imported.'));
            return;
        }

        const labels = failedSteps
            .map(function (item) {
                return item.label || '';
            })
            .filter(Boolean);
        const summary = i18n(
            'completedWithErrors',
            'Import process completed with errors. The following could not be imported: %s.'
        ).replace('%s', labels.join(', '));

        showNotice('error', summary, failedSteps);
    }

    function processNext(jobId, total, processed) {
        const stepIds = getStepIds();
        const nextItemId = stepIds[processed] || stepIds[stepIds.length - 1];
        updateProgress(processed, total, nextItemId);

        return post(config.nextAction, config.nextNonce, { jobId: jobId }).then(function (result) {
            const data = result.data || {};

            if (data.done) {
                updateProgress(data.processed || data.total || 0, data.total || total, data.currentItemId);
                finish(data);
                return;
            }

            if (!result.success) {
                throw new Error(extractErrorMessage(data));
            }

            return processNext(
                data.jobId || jobId,
                data.total || total,
                data.processed || 0
            );
        });
    }

    function startImport() {
        if (isRunning) {
            return;
        }

        if (!config.ajaxUrl || !config.startAction || !config.nextAction) {
            showNotice('error', i18n('genericError', 'Something went wrong.'));
            return;
        }

        setBusy(true);
        updateProgress(0, getStepIds().length, getStepIds()[0]);

        post(config.startAction, config.startNonce)
            .then(function (result) {
                const data = result.data || {};
                if (!result.success || !data.jobId) {
                    throw new Error(extractErrorMessage(data));
                }

                if (data.done) {
                    finish(data);
                    return;
                }

                return processNext(data.jobId, data.total || getStepIds().length, data.processed || 0);
            })
            .catch(function (error) {
                setBusy(false);
                showNotice(
                    'error',
                    error && error.message
                        ? error.message
                        : i18n('genericError', 'Something went wrong.')
                );
            });
    }
});
