/**
 * Helper factories to keep DataTable column configs declarative.
 */
export function textColumn(options = {}) {
    if (!options.key) {
        throw new Error('textColumn requires a key');
    }

    const {
        key,
        header,
        field = key,
        style,
        bodyClass,
        class: columnClass,
        formatter,
        emptyValue = '',
        body,
        ...rest
    } = options;

    const defaultBody = ({ data }) => {
        const value = data?.[field];
        if (typeof formatter === 'function') {
            return formatter({ data, value });
        }
        return value ?? emptyValue;
    };

    return {
        key,
        header,
        field,
        style,
        bodyClass,
        class: columnClass,
        ...rest,
        body: body || defaultBody,
    };
}
