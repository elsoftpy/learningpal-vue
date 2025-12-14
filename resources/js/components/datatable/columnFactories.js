import { defineComponent, h, ref, watch } from 'vue';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import DateInput from '@/components/form/DateInput.vue';
import ResourceViewerCell from '@/components/datatable/ResourceViewerCell.vue';

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
        filterable = false,
        filterPlaceholder = '',
        filterComponent,
        filterProps,
        ...rest
    } = options;

    const defaultBody = ({ data }) => {
        const value = data?.[field];
        if (typeof formatter === 'function') {
            return formatter({ data, value });
        }
        return value ?? emptyValue;
    };

    const column = {
        key,
        header,
        field,
        style,
        bodyClass,
        class: columnClass,
        ...rest,
        body: body || defaultBody,
    };

    if (filterable) {
        column.showFilterMenu = false;
        column.filter = filterComponent || createTextFilterRenderer({ placeholder: filterPlaceholder });
        if (filterProps) {
            column.filterProps = filterProps;
        }
    }

    return column;
}

export function textWithAvatarColumn(options = {}) {
    const {
        key = 'full_name',
        header,
        placeholder = 'Search',
        fieldName = 'full_name',
        avatarField = 'avatar_url',
        style,
        filterable = true,
        filterPlaceholder = placeholder,
        filterComponent,
        filterProps,
    } = options;

    const column = {
        key,
        header,
        style,
        body: createAvatarNameRenderer({ fieldName, avatarField }),
    };

    if (filterable) {
        column.showFilterMenu = false;
        column.filter = filterComponent || createTextFilterRenderer({ placeholder: filterPlaceholder });
        if (filterProps) {
            column.filterProps = filterProps;
        }
    }

    return column;
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

export function dateColumn(options = {}) {
    if (!options.key) {
        throw new Error('dateColumn requires a key');
    }

    const {
        key,
        header,
        field = key,
        filterField = field,
        style,
        locale,
        formatOptions = { dateStyle: 'medium' },
        filterable = false,
        filterPlaceholder = '',
        filterInputId = `${key}-filter-input`,
        valueFormatter,
    } = options;

    const column = {
        key,
        header,
        field,
        style,
        body: ({ data }) =>
            typeof valueFormatter === 'function'
                ? valueFormatter(data?.[field])
                : formatDateValue(data?.[field], locale, formatOptions),
    };

    if (filterField) {
        column.filterField = filterField;
    }

    if (filterable) {
        column.showFilterMenu = false;
        column.filter = createDateFilterRenderer({
            placeholder: filterPlaceholder,
            locale,
            inputId: filterInputId,
        });
    }

    return column;
}

export function resourceViewerColumn(options = {}) {
    const {
        key = 'resource',
        header,
        resourceField = 'resource',
        viewLabel = 'View',
        emptyLabel = 'None',
        buttonIcon = 'pi pi-eye',
        modalTitle,
        style,
    } = options;

    return {
        key,
        header,
        style,
        bodyComponent: ResourceViewerCell,
        bodyProps: {
            resourceField,
            viewLabel,
            emptyLabel,
            buttonIcon,
            modalTitle,
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

const ISO_DATE_REGEX = /^(\d{4})-(\d{2})-(\d{2})$/; // YYYY-MM-DD

function formatDateValue(value, locale, formatOptions) {
    const date = normalizeDateInput(value);
    if (!date) {
        return '';
    }

    return new Intl.DateTimeFormat(locale || undefined, formatOptions).format(date);
}

function createDateFilterRenderer({ placeholder, locale, inputId }) {
    return ({ filterModel, filterCallback }) => {
        const handleUpdate = (isoValue) => {
            if (filterModel) {
                filterModel.value = isoValue;
            }
            if (typeof filterCallback === 'function') {
                filterCallback();
            }
        };

        return h(DateFilterField, {
            modelValue: filterModel?.value ?? null,
            placeholder,
            locale,
            inputId,
            'onUpdate:modelValue': handleUpdate,
        });
    };
}

const DateFilterField = defineComponent({
    name: 'DateFilterField',
    props: {
        modelValue: {
            type: String,
            default: null,
        },
        placeholder: {
            type: String,
            default: '',
        },
        locale: {
            type: String,
            default: undefined,
        },
        inputId: {
            type: String,
            required: true,
        },
    },
    emits: ['update:modelValue'],
    setup(props, { emit }) {
        const localValue = ref(formatDisplayFromIso(props.modelValue, props.locale));

        watch(
            () => props.modelValue,
            (value) => {
                const next = formatDisplayFromIso(value, props.locale);
                if (next !== localValue.value) {
                    localValue.value = next;
                }
            }
        );

        watch(
            () => props.locale,
            (value) => {
                const next = formatDisplayFromIso(props.modelValue, value);
                if (next !== localValue.value) {
                    localValue.value = next;
                }
            }
        );

        const handleMaskedUpdate = (value) => {
            localValue.value = value || '';
            if (!value) {
                emit('update:modelValue', null);
            }
        };

        const handleUnmaskedUpdate = (digits) => {
            if (!digits) {
                emit('update:modelValue', null);
                return;
            }

            if (digits.length === 8) {
                const iso = digitsToIso(digits, props.locale);
                emit('update:modelValue', iso);
            }
        };

        return () =>
            h(DateInput, {
                id: props.inputId,
                name: props.inputId,
                label: null,
                placeholder: props.placeholder,
                modelValue: localValue.value,
                'onUpdate:modelValue': handleMaskedUpdate,
                'onUpdate:unmasked': handleUnmaskedUpdate,
            });
    },
});

function digitsToIso(digits, locale) {
    if (digits.length !== 8) {
        return null;
    }

    const monthFirst = usesMonthFirst(locale);
    const first = digits.slice(0, 2);
    const second = digits.slice(2, 4);
    const year = digits.slice(4);

    const month = monthFirst ? first : second;
    const day = monthFirst ? second : first;

    if (!isValidDateParts({ year, month, day })) {
        return null;
    }

    return `${year}-${month}-${day}`;
}

function formatDisplayFromIso(value, locale) {
    if (!value) {
        return '';
    }

    const match = typeof value === 'string' ? value.match(ISO_DATE_REGEX) : null;
    if (!match) {
        return '';
    }

    const [, year, month, day] = match;

    return usesMonthFirst(locale)
        ? `${month}-${day}-${year}`
        : `${day}/${month}/${year}`;
}

function usesMonthFirst(locale) {
    if (!locale) {
        return true;
    }

    return locale.toLowerCase().startsWith('en');
}

function isValidDateParts({ year, month, day }) {
    const yearNum = Number(year);
    const monthNum = Number(month);
    const dayNum = Number(day);

    if (!yearNum || monthNum < 1 || monthNum > 12 || dayNum < 1 || dayNum > 31) {
        return false;
    }

    const date = new Date(yearNum, monthNum - 1, dayNum);
    return (
        date.getFullYear() === yearNum &&
        date.getMonth() + 1 === monthNum &&
        date.getDate() === dayNum
    );
}

function normalizeDateInput(value) {
    if (!value) {
        return null;
    }

    if (value instanceof Date) {
        return Number.isNaN(value.getTime()) ? null : value;
    }

    if (typeof value === 'string') {
        const parsed = parseISODateString(value);
        if (parsed) {
            return parsed;
        }

        const timestamp = Date.parse(value);
        if (!Number.isNaN(timestamp)) {
            return new Date(timestamp);
        }

        return null;
    }

    const date = new Date(value);
    return Number.isNaN(date.getTime()) ? null : date;
}

function parseISODateString(value) {
    const match = typeof value === 'string' ? value.match(ISO_DATE_REGEX) : null;
    if (!match) {
        return null;
    }

    const [, year, month, day] = match;
    const date = new Date(Number(year), Number(month) - 1, Number(day));
    return Number.isNaN(date.getTime()) ? null : date;
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
