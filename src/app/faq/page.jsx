'use client';
import { useState } from 'react';
import { faqData } from '@/lib/mockData';

export default function FAQPage() {
    const [activeItems, setActiveItems] = useState({});
    const toggle = (key) => setActiveItems(prev => ({ ...prev, [key]: !prev[key] }));

    return (
        <div className="container">
            <div className="page-hero">
                <h1>Frequently Asked Questions</h1>
                <p>Find answers to common questions about Sneakerheads</p>
            </div>
            <div className="faq-container">
                {faqData.map((section, si) => (
                    <div key={si} className="faq-section">
                        <h2>{section.category}</h2>
                        {section.items.map((item, qi) => {
                            const key = `${si}-${qi}`;
                            return (
                                <div key={key} className={`faq-item${activeItems[key] ? ' active' : ''}`}>
                                    <div className="faq-question" onClick={() => toggle(key)}>
                                        <span>{item.q}</span>
                                        <i className="fas fa-chevron-down"></i>
                                    </div>
                                    <div className="faq-answer"><p>{item.a}</p></div>
                                </div>
                            );
                        })}
                    </div>
                ))}
            </div>
        </div>
    );
}
