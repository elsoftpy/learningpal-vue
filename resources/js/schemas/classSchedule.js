import { z } from 'zod';

export const createClassScheduleSchema = (t) => {
    return z.object({
        name: z.string()
            .min(1, { message: t('Schedule Name is required.') }),
        courses: z.number()
            .min(1, { message: t('At least one course must be selected.') }),
        start_month: z.string()
            .refine((val) => {
                if (!val || val.trim() === '') return true;

                // Validate format MM/YYYY
                const regex = /^(0[1-9]|1[0-2])\/\d{4}$/;
                if (!regex.test(val)) return false;

                const [month, year] = val.split('/');

                const date = new Date(`${year}-${month}-01`);

                return date instanceof Date && !isNaN(date);
            }, {
                message: t('Invalid month format. Use MM/YYYY.'),
            })
            .min(1, { message: t('Start Month is required.') }),
    });
};