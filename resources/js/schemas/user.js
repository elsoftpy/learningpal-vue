import { z } from 'zod';

export const createUserSchema = (t) => {
  return z.object({
    first_name: z.string().min(1, t('First Name is required')),
    last_name: z.string().min(1, t('Last Name is required')),
    email: z.string().min(1, t('Email is required')),
    email: z.email(t('Invalid email address')),
    phone: z.string().optional(),
    address: z.string().optional(),
    birth_date: z.string().optional(),
    avatar: z.url(t('Avatar must be a valid URL')).optional()
  });
}