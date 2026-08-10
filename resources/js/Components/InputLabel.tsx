import { LabelHTMLAttributes, PropsWithChildren } from 'react';

export default function InputLabel({ 
    value, 
    className = '', 
    children, 
    required, 
    ...props 
}: PropsWithChildren<LabelHTMLAttributes<HTMLLabelElement> & { value?: string; required?: boolean }>) {
    return (
        <label
            {...props}
            className={`block font-medium text-sm text-gray-700 dark:text-gray-300 ` + className}
        >
            {value ? value : children}
            {required && <span className="text-red-500 ml-1">*</span>}
        </label>
    );
}
