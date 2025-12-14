import { h } from 'vue';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import Button from 'primevue/button';

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

export function textWithAvatarColumn(options = {}) {
    const {
        key = 'full_name',
        header,
        placeholder = 'Search',
        fieldName = 'full_name',
        avatarField = 'avatar_url',
        style,
    } = options;

    return {
        key,
        header,
        style,
        showFilterMenu: false,
        filter: createTextFilterRenderer({ placeholder }),
        body: createAvatarNameRenderer({ fieldName, avatarField }),
    };
}

export function tagsArrayColumn(options = {}) {
    const {
        key = 'roles',
        header,
        itemsField = 'display_roles',
        emptyLabel = 'None',
        style,
    } = options;

    return {
        key,
        header,
        style,
        body: ({ data }) => {
            const items = Array.isArray(data?.[itemsField]) ? data[itemsField] : [];

            if (!items.length) {
                return h('span', { class: 'text-gray-400 text-sm' }, emptyLabel);
            }

            return h('div', null,
                items.map((item) =>
                    h(Tag, {
                        key: item,
                        value: item,
                        severity: 'info',
                        class: 'mr-2 mb-1',
                    })
                )
            );
        },
    };
}

export function statusTagColumn(options = {}) {
    const {
        key = 'status',
        header,
        statusField = 'status',
        displayField = 'display_status',
        style,
        severityResolver = defaultStatusSeverity,
    } = options;

    return {
        key,
        header,
        style,
        body: ({ data }) =>
            h(Tag, {
                value: data?.[displayField] ?? data?.[statusField] ?? '',
                severity: severityResolver(data?.[statusField]),
            }),
    };
}

export function paymentColumn(options = {}) {
    const {
        key = 'payment',
        header,
        receiptField = 'payment_receipt',
        onViewReceipt,
        viewLabel = 'View',
        emptyLabel = 'None',
        style,
    } = options;

    return {
        key,
        header,
        style,
        body: ({ data }) => {
            const receipt = data?.[receiptField];

            if (!receipt) {
                return h('span', { class: 'text-gray-400 text-sm' }, emptyLabel);
            }

            return h(Button, {
                type: 'button',
                label: viewLabel,
                icon: 'pi pi-eye',
                size: 'small',
                onClick: () => typeof onViewReceipt === 'function' && onViewReceipt(data),
            });
        },
    };
}

function createTextFilterRenderer({ placeholder }) {
    return ({ filterModel, filterCallback }) => {
        const handleUpdate = (value) => {
            if (filterModel) {
                filterModel.value = value;
            }
            if (typeof filterCallback === 'function') {
                filterCallback();
            }
        };

        return h(InputText, {
            modelValue: filterModel?.value ?? '',
            'onUpdate:modelValue': handleUpdate,
            type: 'text',
            placeholder,
            class: 'w-full',
        });
    };
}

function createAvatarNameRenderer({ fieldName, avatarField }) {
    return ({ data }) =>
        h('div', { class: 'flex items-center space-x-2' }, [
            h('img', {
                src: data?.[avatarField],
                alt: data?.[fieldName] || '',
                class: 'w-10 h-10 rounded-full object-cover',
            }),
            h('span', null, data?.[fieldName] ?? ''),
        ]);
}

export function defaultStatusSeverity(status) {
    switch (status) {
        case 'active':
            return 'success';
        case 'disabled':
            return 'danger';
        case 'pending':
            return 'warn';
        default:
            return 'info';
    }
}
