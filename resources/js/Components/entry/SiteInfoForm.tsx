import type { SiteInfo } from '@/types/daily-entry';
import { Card, Col, Form, Input, InputNumber, Row } from 'antd';

interface SiteInfoFormProps {
    data: SiteInfo | null;
    onChange: (data: Partial<SiteInfo>) => void;
    disabled?: boolean;
}

export default function SiteInfoForm({ data, onChange, disabled }: SiteInfoFormProps) {
    const values = data ?? {};

    const setField = (field: keyof SiteInfo, value: string | number | null) => {
        onChange({ ...values, [field]: value });
    };

    return (
        <Card title="Info Site" size="small">
            <Form layout="vertical" disabled={disabled}>
                <Row gutter={16}>
                    <Col xs={24} md={12}>
                        <Form.Item label="Cuaca">
                            <Input
                                value={values.weather ?? ''}
                                onChange={(e) => setField('weather', e.target.value || null)}
                                placeholder="Cerah / Hujan / Berawan"
                            />
                        </Form.Item>
                    </Col>
                    <Col xs={24} md={6}>
                        <Form.Item label="Jam Hujan">
                            <InputNumber
                                min={0}
                                max={24}
                                value={values.rain_hours}
                                onChange={(v) => setField('rain_hours', v)}
                                style={{ width: '100%' }}
                            />
                        </Form.Item>
                    </Col>
                    <Col xs={24} md={6}>
                        <Form.Item label="Jam Licin">
                            <InputNumber
                                min={0}
                                max={24}
                                value={values.slippery_hours}
                                onChange={(v) => setField('slippery_hours', v)}
                                style={{ width: '100%' }}
                            />
                        </Form.Item>
                    </Col>
                    <Col xs={24} md={6}>
                        <Form.Item label="Manpower Plan">
                            <InputNumber
                                min={0}
                                value={values.manpower_plan}
                                onChange={(v) => setField('manpower_plan', v)}
                                style={{ width: '100%' }}
                            />
                        </Form.Item>
                    </Col>
                    <Col xs={24} md={6}>
                        <Form.Item label="Manpower Aktual">
                            <InputNumber
                                min={0}
                                value={values.manpower_actual}
                                onChange={(v) => setField('manpower_actual', v)}
                                style={{ width: '100%' }}
                            />
                        </Form.Item>
                    </Col>
                    <Col xs={24} md={6}>
                        <Form.Item label="Stok BBM (Liter)">
                            <InputNumber
                                min={0}
                                value={values.fuel_stock_liters}
                                onChange={(v) => setField('fuel_stock_liters', v)}
                                style={{ width: '100%' }}
                            />
                        </Form.Item>
                    </Col>
                    <Col xs={24}>
                        <Form.Item label="Catatan Keselamatan">
                            <Input.TextArea
                                rows={4}
                                value={values.safety_notes ?? ''}
                                onChange={(e) => setField('safety_notes', e.target.value || null)}
                            />
                        </Form.Item>
                    </Col>
                </Row>
            </Form>
        </Card>
    );
}
