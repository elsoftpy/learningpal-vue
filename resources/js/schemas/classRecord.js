import { z } from 'zod';

export const createClassRecordSchema = (t, locale = 'es') => {
    const dateRegex = locale === 'en'
        ? /^(0[1-9]|1[0-2])-([0][1-9]|[12][0-9]|3[01])-\d{4}$/
        : /^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/\d{4}$/;

    return z.object({
        class_schedule_detail_id: z.coerce.number({
            required_error: t('Class schedule detail is required.'),
            invalid_type_error: t('Class schedule detail is required.'),
        }).min(1, { message: t('Class schedule detail is required.') }),
        teacher_id: z.coerce.number({
            required_error: t('Teacher is required.'),
            invalid_type_error: t('Teacher is required.'),
        }).min(1, { message: t('Teacher is required.') }),
        date: z.string()
            .min(1, { message: t('Date is required.') })
            .refine((value) => dateRegex.test(value), {
                message: t('Invalid date format.'),
            }),
        start_time: z.string()
            .min(1, { message: t('Start time is required.') })
            .regex(/^([01]\d|2[0-3]):[0-5]\d$/, { message: t('Use HH:MM format.') }),
        end_time: z.string()
            .min(1, { message: t('End time is required.') })
            .regex(/^([01]\d|2[0-3]):[0-5]\d$/, { message: t('Use HH:MM format.') }),
        comments: z.string()
            .trim()
            .min(1, { message: t('Comments are required.') })
            .max(255, { message: t('Comments are too long.') }),
    });
};
