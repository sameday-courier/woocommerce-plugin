(function (window) {
    'use strict';

    /**
     * Runs a server-side recursive bulk job (start action + next action loop).
     *
     * @param {Object} options
     * @param {string} options.ajaxUrl
     * @param {string} options.startAction
     * @param {string} options.startNonce
     * @param {string} options.nextAction
     * @param {string} options.nextNonce
     * @param {Object} [options.startData]
     * @param {function(Object): void} [options.onStart]
     * @param {function(Object): void} [options.onProgress]
     * @param {function(Object): void} options.onComplete
     * @param {function(string): void} [options.onError]
     * @returns {Promise<void>}
     */
    function run(options) {
        var startData = options.startData || {};

        return post(options.ajaxUrl, options.startAction, options.startNonce, startData)
            .then(function (result) {
                var data = result.data || {};

                if (typeof options.onStart === 'function') {
                    options.onStart(data);
                }

                if (!result.success && !data.jobId) {
                    throw new Error(extractErrorMessage(data, options.genericError));
                }

                if (!data.jobId) {
                    if (data.done) {
                        options.onComplete(data);
                        return;
                    }

                    throw new Error(extractErrorMessage(data, options.genericError));
                }

                return processNext(options, data.jobId, data);
            })
            .catch(function (error) {
                if (typeof options.onError === 'function') {
                    options.onError(error && error.message ? error.message : String(error));
                }
            });
    }

    /**
     * @param {Object} options
     * @param {string} jobId
     * @param {Object} data
     * @returns {Promise<void>}
     */
    function processNext(options, jobId, data) {
        if (data.done) {
            options.onComplete(data);
            return Promise.resolve();
        }

        if (typeof options.onProgress === 'function') {
            options.onProgress(data);
        }

        return post(options.ajaxUrl, options.nextAction, options.nextNonce, { jobId: jobId })
            .then(function (result) {
                var payload = result.data || {};

                if (payload.done) {
                    options.onComplete(payload);
                    return;
                }

                if (!result.success) {
                    if (payload.done) {
                        options.onComplete(payload);
                        return;
                    }

                    throw new Error(extractErrorMessage(payload, options.genericError));
                }

                return processNext(options, jobId, payload);
            });
    }

    /**
     * @param {string} ajaxUrl
     * @param {string} action
     * @param {string} nonce
     * @param {Object} extra
     * @returns {Promise<{success: boolean, data: Object}>}
     */
    function post(ajaxUrl, action, nonce, extra) {
        var body = new URLSearchParams();
        body.append('action', action);
        body.append('_wpnonce', nonce);

        Object.keys(extra || {}).forEach(function (key) {
            var value = extra[key];
            if (Array.isArray(value)) {
                value.forEach(function (item) {
                    body.append(key + '[]', String(item));
                });
                return;
            }

            body.append(key, String(value));
        });

        return fetch(ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: body.toString()
        }).then(function (response) {
            return response.json().then(function (json) {
                return {
                    success: !!(json && json.success),
                    data: json && json.data !== undefined ? json.data : {}
                };
            }, function () {
                return {
                    success: false,
                    data: {}
                };
            });
        });
    }

    /**
     * @param {*} data
     * @param {string} fallback
     * @returns {string}
     */
    function extractErrorMessage(data, fallback) {
        if (typeof data === 'string' && data !== '') {
            return data;
        }

        if (data && typeof data.message === 'string' && data.message !== '') {
            return data.message;
        }

        return fallback || 'Something went wrong.';
    }

    window.SamedayRecursiveJob = {
        run: run,
        post: post,
        extractErrorMessage: extractErrorMessage
    };
}(window));
