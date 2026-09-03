document.addEventListener('DOMContentLoaded', function () {
    var actions = document.querySelector('.sameday-settings-actions');
    var submitRow = document.querySelector('#mainform p.submit');

    if (actions && submitRow) {
        submitRow.insertAdjacentElement('afterend', actions);
    }

    var overlay = document.getElementById('sameday-all-import-overlay');
    if (overlay && overlay.parentElement !== document.body) {
        document.body.appendChild(overlay);
    }

    var importButton = document.getElementById('sameday-all-import-button');
    if (!importButton || !overlay) {
        return;
    }

    var config = window.samedayAllImport || {};
    var statusNode = overlay.querySelector('[data-sameday-all-import-status]');
    var progressNode = overlay.querySelector('[data-sameday-all-import-progress]');
    var isRunning = false;

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
        var steps = config.steps || {};

        return steps[itemId] || steps[String(itemId)] || '';
    }

    function formatProcessing(itemId, processed, total) {
        var template = i18n('processing', 'Importing %s (%s/%s)');
        var current = Math.min((processed || 0) + 1, total || 0);

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
        var safeTotal = total || 0;
        var safeProcessed = processed || 0;
        var percent = safeTotal > 0 ? Math.round((safeProcessed / safeTotal) * 100) : 0;

        if (statusNode) {
            statusNode.textContent = formatProcessing(itemId, safeProcessed, safeTotal);
        }

        if (progressNode) {
            progressNode.style.width = percent + '%';
        }
    }

    function showNotice(type, message, failedSteps) {
        var wrap = document.querySelector('.wrap');
        if (!wrap) {
            return;
        }

        var previous = wrap.querySelectorAll('.sameday-all-import-notice');
        previous.forEach(function (node) {
            node.remove();
        });

        var notice = document.createElement('div');
        notice.className = 'notice notice-' + type + ' is-dismissible sameday-all-import-notice';

        var paragraph = document.createElement('p');
        paragraph.textContent = message;
        notice.appendChild(paragraph);

        (failedSteps || []).forEach(function (failedStep) {
            var detail = document.createElement('p');
            var label = document.createElement('strong');
            label.textContent = (failedStep.label || '') + ':';
            detail.appendChild(label);
            detail.appendChild(document.createTextNode(' ' + (failedStep.message || '')));
            notice.appendChild(detail);
        });

        var heading = wrap.querySelector('h1, h2');
        if (heading) {
            heading.insertAdjacentElement('afterend', notice);
        } else {
            wrap.insertAdjacentElement('afterbegin', notice);
        }

        notice.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function finish(data) {
        setBusy(false);

        var items = Array.isArray(data.items) ? data.items : [];
        var failedSteps = items.filter(function (item) {
            return item && item.status === 'error';
        });

        if (failedSteps.length === 0) {
            showNotice('success', i18n('complete', 'Process complete, all data is imported.'));
            return;
        }

        var labels = failedSteps
            .map(function (item) {
                return item.label || '';
            })
            .filter(Boolean);
        var summary = i18n(
            'completedWithErrors',
            'Import process completed with errors. The following could not be imported: %s.'
        ).replace('%s', labels.join(', '));

        showNotice('error', summary, failedSteps);
    }

    function resolveProgressItemId(data, fallbackStepIds) {
        if (data.currentItemId) {
            return data.currentItemId;
        }

        var processed = data.processed || 0;
        return fallbackStepIds[processed] || fallbackStepIds[fallbackStepIds.length - 1];
    }

    function startImport() {
        if (isRunning) {
            return;
        }

        if (!config.ajaxUrl || !config.startAction || !config.nextAction) {
            showNotice('error', i18n('genericError', 'Something went wrong.'));
            return;
        }

        var stepIds = getStepIds();

        setBusy(true);
        updateProgress(0, stepIds.length, stepIds[0]);

        window.SamedayRecursiveJob.run({
            ajaxUrl: config.ajaxUrl,
            startAction: config.startAction,
            startNonce: config.startNonce,
            nextAction: config.nextAction,
            nextNonce: config.nextNonce,
            genericError: i18n('genericError', 'Something went wrong.'),
            onProgress: function (data) {
                updateProgress(
                    data.processed || 0,
                    data.total || stepIds.length,
                    resolveProgressItemId(data, stepIds)
                );
            },
            onComplete: function (data) {
                updateProgress(
                    data.processed || data.total || 0,
                    data.total || stepIds.length,
                    resolveProgressItemId(data, stepIds)
                );
                finish(data);
            },
            onError: function (message) {
                setBusy(false);
                showNotice('error', message);
            }
        });
    }
});
