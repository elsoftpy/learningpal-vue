// schemas/login.js
import { z } from 'zod';

export const loginSchema = z.object({
  name: z.string().min(1, 'Username is required'),
  password: z.string().min(1, 'Password is required'),
  remember: z.boolean().default(false)
});

// Optional: TypeScript type inference
// export type LoginFormInput = z.infer<typeof loginSchema>;