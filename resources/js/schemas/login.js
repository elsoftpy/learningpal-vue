// schemas/login.js
import { z } from 'zod';

export const createLoginSchema = (t) => {
  return z.object({
    name: z.string().min(1, t('Username is required')),
    password: z.string().min(1, t('Password is required')),
    remember: z.boolean().default(false)
  });
};
