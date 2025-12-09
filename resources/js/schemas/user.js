import { locales, z } from 'zod';

export const createUserSchema = (t, locale) => {
  const dataRegex = locale === 'en' ? /^\d{2}-\d{2}-\d{4}$/ : /^\d{2}\/\d{2}\/\d{4}$/;


  return z.object({
    first_name: z.string().min(1, t('First Name is required')),
    last_name: z.string().min(1, t('Last Name is required')),
    email: z.email(t('Invalid email address'))
      .min(1, t('Email is required')),
    phone: z.string().optional(),
    address: z.string().optional(),
    birth_date: z.string()
      .refine((val) => {
        
        if (!val || val.trim() === '') return true;

        const regex = dataRegex;
        if (!regex.test(val)) return false;

        const [day, month, year] = locale === 'en' ? val.split('-') : val.split('/');
        const date = new Date(`${year}-${month}-${day}`);

        return date instanceof Date && !isNaN(date);
      }, {
        message: t('Invalid date format'),
      })
      .optional(),
    avatar: z.url(t('Avatar must be a valid URL')).optional(),
    name: z.string().min(1, t('Username is required')),
    password: z.string().optional(),
    roles: z.array(z.string()).min(1, t('Select at least one role')),
    status: z.string().min(1, t('Select Status')),
  });
}