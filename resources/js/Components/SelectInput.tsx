import { forwardRef, SelectHTMLAttributes } from 'react';

interface SelectInputProps extends SelectHTMLAttributes<HTMLSelectElement> {
    options?: { value: string | number; label: string }[];
    placeholder?: string;
}

export default forwardRef<HTMLSelectElement, SelectInputProps>(function SelectInput(
    { className = '', children, options, placeholder, ...props },
    ref
) {
    return (
        <select
            {...props}
            className={
                'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm ' +
                className
            }
            ref={ref}
        >
            {placeholder && <option value="">{placeholder}</option>}
            {options && options.map((option, index) => (
                <option key={index} value={option.value}>
                    {option.label}
                </option>
            ))}
            {children}
        </select>
    );
});
