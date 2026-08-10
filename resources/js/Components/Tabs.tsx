import { Link } from '@inertiajs/react';
import { LucideIcon } from 'lucide-react';

interface Tab {
    label: string;
    href: string;
    active?: boolean;
    icon?: LucideIcon;
}

interface TabsProps {
    tabs: Tab[];
}

export default function Tabs({ tabs }: TabsProps) {
    return (
        <div className="mb-6 border-b border-gray-200 dark:border-gray-700">
            <nav className="-mb-px flex space-x-8 overflow-x-auto">
                {tabs.map((tab, index) => {
                    const Icon = tab.icon;
                    return (
                        <Link
                            key={index}
                            href={tab.href}
                            className={`flex items-center whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium transition ${
                                tab.active
                                    ? 'border-indigo-500 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400'
                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:text-gray-300'
                            }`}
                        >
                            {Icon && <Icon className="mr-2 h-4 w-4" />}
                            {tab.label}
                        </Link>
                    );
                })}
            </nav>
        </div>
    );
}
