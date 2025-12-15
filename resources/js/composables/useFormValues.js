export function useFormValues() {
    const extractFormData = (formData) => {
        if (!formData) {
            return {valid: false, values: {}};
        }

        const values = formData.states
            ?   Object.keys(formData.states).reduce((acc, key) => {
                    acc[key] = formData.states[key].value;
                    return acc;
                }, {})
            :   {};

        return {
            valid: formData.valid || false,
            values
        };
    };

    return {
        extractFormData
    };
}