# app/models/prompts.py
from pydantic import BaseModel, Field
from typing import List, Optional, Dict, Union
from enum import Enum

class ProfileType(str, Enum):
    INDIVIDUAL = "individual"
    GROUP = "group"

class MaterialType(str, Enum):
    LEARNING_TASKS = "learning_tasks"
    SUPPORT_INFO = "support_info"
    PROCEDURAL_INFO = "procedural_info"
    VERBAL_PROTOCOLS = "verbal_protocols"
    EXAMPLE = "example"

class CltEffectCategory(str, Enum):
    NEW_KNOWLEDGE = "new_knowledge"
    PRIOR_KNOWLEDGE = "prior_knowledge"

class CltEffect(BaseModel):
    id: str
    name: str
    description: str
    category: CltEffectCategory
    application_guidance: str

class LearningObjective(BaseModel):
    id: int
    description: str
    bloom_level: Optional[str] = None

class StudentProfile(BaseModel):
    """Student individual profile data"""
    student_id: int
    student_name: Optional[str] = None
    knowledge_level: str
    motivation_level: str
    learning_strategies: str
    knowledge_scores: Optional[Dict] = None
    motivation_scores: Optional[Dict] = None
    strategy_scores: Optional[Dict] = None

class GroupProfile(BaseModel):
    """Group profile summary data"""
    group_summary: Dict
    knowledge_averages: Optional[Dict] = None
    motivation_averages: Optional[Dict] = None
    strategy_averages: Optional[Dict] = None
    teaching_recommendations: Optional[List[str]] = None
    total_students: Optional[int] = None

class MaterialGenerationRequest(BaseModel):
    course_id: int
    profile_type: ProfileType
    profile_data: Union[Dict, StudentProfile, GroupProfile]
    student_id: Optional[int] = None
    learning_objectives: List[LearningObjective]
    selected_clt_effects: List[str]
    material_type: MaterialType
    topic: str
    additional_context: Optional[str] = None

class GeneratedMaterial(BaseModel):
    material_type: MaterialType
    content: Dict
    metadata: Dict
    token_usage: Optional[Dict] = None