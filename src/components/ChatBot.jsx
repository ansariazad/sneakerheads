'use client';
import { useState, useRef, useEffect } from 'react';

const botResponses = {
    greeting: [
        "Hey! 👋 Welcome to Sneakerheads! How can I help you today?",
        "Hi there! I'm here to help you find the perfect pair. What are you looking for?",
    ],
    shipping: "🚚 We offer **free shipping** on all orders above ₹5,000. Standard delivery takes 3-5 business days across India. Express delivery (1-2 days) is available for ₹299.",
    returns: "🔄 We have a **7-day easy return** policy. If the sneakers don't fit or have any issues, just initiate a return from your Orders page. Refund is processed within 3-5 business days.",
    payment: "💳 We accept **UPI** (GPay, PhonePe, Paytm), and soon COD. UPI payments are instant and secure. Just scan the QR code or enter our UPI ID at checkout!",
    authentic: "✅ **100% Authentic Guaranteed.** Every pair is verified by our team before shipping. We check serial numbers, materials, and packaging. Fake? Full refund, no questions asked.",
    sell: "📦 Want to sell sneakers? Go to **Seller Dashboard** from the menu. Upload photos of your sneakers and our AI will auto-detect the brand, model, and suggest a price! It's free to list.",
    size: "📏 We follow **UK sizing**. Check the size chart on each product page. Pro tip: Jordan and Nike run slightly small — go half size up for comfort!",
    discount: "🎉 Use code **FIRST15** for 15% off your first order! We also have free shipping on orders above ₹5,000. Check the Offers section on our homepage for more deals.",
    contact: "📞 You can reach us at **support@sneakerheads.in** or DM us on Instagram @sneakerheads.in. Our support team responds within 2 hours!",
    brands: "👟 We carry **Nike, Adidas, Jordan, Puma, New Balance, Reebok, Converse, Vans, Asics**, and Indian brands like **Campus, Sparx, Red Tape, and Woodland**!",
};

const quickReplies = [
    { label: '🚚 Shipping', key: 'shipping' },
    { label: '🔄 Returns', key: 'returns' },
    { label: '💳 Payment', key: 'payment' },
    { label: '✅ Authenticity', key: 'authentic' },
    { label: '📦 How to Sell', key: 'sell' },
    { label: '📏 Size Guide', key: 'size' },
    { label: '🎉 Discounts', key: 'discount' },
    { label: '👟 Brands', key: 'brands' },
];

function getResponse(text) {
    const lower = text.toLowerCase();
    if (lower.match(/hi|hello|hey|sup/)) return botResponses.greeting[Math.floor(Math.random() * botResponses.greeting.length)];
    if (lower.match(/ship|deliver|track/)) return botResponses.shipping;
    if (lower.match(/return|refund|exchange/)) return botResponses.returns;
    if (lower.match(/pay|upi|cod|cash/)) return botResponses.payment;
    if (lower.match(/authentic|fake|real|genuine|original/)) return botResponses.authentic;
    if (lower.match(/sell|list|upload/)) return botResponses.sell;
    if (lower.match(/size|fit|chart/)) return botResponses.size;
    if (lower.match(/discount|offer|coupon|code|promo/)) return botResponses.discount;
    if (lower.match(/contact|support|help|email|phone/)) return botResponses.contact;
    if (lower.match(/brand|nike|adidas|puma|jordan/)) return botResponses.brands;
    return "🤔 I'm not sure about that. Try asking about **shipping**, **returns**, **payments**, **sizes**, **authenticity**, or **how to sell**. Or contact us at support@sneakerheads.in!";
}

export default function ChatBot() {
    const [isOpen, setIsOpen] = useState(false);
    const [messages, setMessages] = useState([
        { from: 'bot', text: botResponses.greeting[0] }
    ]);
    const [input, setInput] = useState('');
    const [isTyping, setIsTyping] = useState(false);
    const chatEndRef = useRef(null);

    useEffect(() => {
        chatEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    }, [messages, isTyping]);

    const sendMessage = (text) => {
        if (!text.trim()) return;
        setMessages(prev => [...prev, { from: 'user', text }]);
        setInput('');
        setIsTyping(true);

        setTimeout(() => {
            setMessages(prev => [...prev, { from: 'bot', text: getResponse(text) }]);
            setIsTyping(false);
        }, 800 + Math.random() * 600);
    };

    const handleQuickReply = (key) => {
        const label = quickReplies.find(q => q.key === key)?.label || key;
        setMessages(prev => [...prev, { from: 'user', text: label }]);
        setIsTyping(true);
        setTimeout(() => {
            setMessages(prev => [...prev, { from: 'bot', text: botResponses[key] }]);
            setIsTyping(false);
        }, 600);
    };

    return (
        <>
            {/* Floating Chat Button */}
            <button className="chatbot-fab" onClick={() => setIsOpen(!isOpen)} title="Customer Support">
                <i className={`fas ${isOpen ? 'fa-times' : 'fa-comment-dots'}`}></i>
            </button>

            {/* Chat Window */}
            {isOpen && (
                <div className="chatbot-window">
                    <div className="chatbot-header">
                        <div className="chatbot-header-info">
                            <div className="chatbot-avatar"><i className="fas fa-robot"></i></div>
                            <div>
                                <h4>SneakerBot</h4>
                                <span className="chatbot-status">● Online</span>
                            </div>
                        </div>
                        <button onClick={() => setIsOpen(false)}><i className="fas fa-times"></i></button>
                    </div>

                    <div className="chatbot-messages">
                        {messages.map((msg, i) => (
                            <div key={i} className={`chat-message ${msg.from}`}>
                                {msg.from === 'bot' && <div className="chat-avatar"><i className="fas fa-robot"></i></div>}
                                <div className="chat-bubble" dangerouslySetInnerHTML={{ __html: msg.text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>') }}></div>
                            </div>
                        ))}
                        {isTyping && (
                            <div className="chat-message bot">
                                <div className="chat-avatar"><i className="fas fa-robot"></i></div>
                                <div className="chat-bubble typing"><span></span><span></span><span></span></div>
                            </div>
                        )}
                        <div ref={chatEndRef} />
                    </div>

                    {/* Quick Replies */}
                    <div className="chatbot-quick-replies">
                        {quickReplies.map(q => (
                            <button key={q.key} onClick={() => handleQuickReply(q.key)}>{q.label}</button>
                        ))}
                    </div>

                    <div className="chatbot-input">
                        <input
                            type="text"
                            value={input}
                            onChange={e => setInput(e.target.value)}
                            onKeyDown={e => e.key === 'Enter' && sendMessage(input)}
                            placeholder="Ask me anything..."
                        />
                        <button onClick={() => sendMessage(input)}><i className="fas fa-paper-plane"></i></button>
                    </div>
                </div>
            )}
        </>
    );
}
