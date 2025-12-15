import { z } from 'zod';

export const createCourseSchema = (t) => {
    return z.object({
        name: z.string()
            .min(1, { message: t('Course Name is required.') }),
        language_id: z.number()
            .min(1, { message: t('Language is required.') }),
        language_level_id: z.number()
            .min(1, { message: t('Level is required.') }),
        chat_room_link: z.url({ message: t('Chat Room Link must be a valid URL.') })
            .optional()
            .or(z.literal('')),
    });
};