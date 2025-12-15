import { z } from 'zod';

export const createCourseSchema = (t) => {
    return z.object({
        name: z.string()
            .min(2, { message: t('Name must be at least 2 characters long.') })
            .max(100, { message: t('Name must be at most 100 characters long.') }),
        language_id: z.number()
            .min(1, { message: t('Language is required.') }),
        language_level_id: z.number()
            .min(1, { message: t('Level is required.') }),
        chat_room_link: z.url({ message: t('Chat Room Link must be a valid URL.') })
            .optional()
            .or(z.literal('')),
    });
};