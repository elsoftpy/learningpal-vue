import { z } from 'zod';

export const createLanguageLevelSchema = (t) => {
    return z.object({
        description: z.string().min(1, t('Language Level is required.')),
        level: z.string().optional(),
        language_id: z.number().min(1, t('Language is required.')),
        status: z.string().min(1, t('Status is required.')),
    });
}