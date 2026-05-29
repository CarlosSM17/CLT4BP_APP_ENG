# app/services/prompt_builder.py
import json
import os
from typing import Dict, List, Union
from app.models.prompts import (
    MaterialGenerationRequest, MaterialType, ProfileType, CltEffect
)
from app.models.profiles import StudentProfile, GroupProfile

class PromptBuilder:
    """
    Construye prompts estructurados para Claude API
    """
    
    # Definición de efectos CLT
    CLT_EFFECTS = {
        # Efectos para Nuevo Conocimiento
        "goal_free": CltEffect(
            id="goal_free",
            name="Goal-Free Effect",
            description="Eliminate specific goals to reduce cognitive load and allow for exploration",            
            category="new_knowledge",
            application_guidance="It presents problems without specific objectives, allowing the student to explore different solutions"
        ),
        "worked_example": CltEffect(
            id="worked_example",
            name="Worked Example Effect",
            description="Show completely solved examples with step-by-step explanations",
            category="new_knowledge",
            application_guidance="Include completely worked examples before similar tasks"
        ),
        "completion_problem": CltEffect(
            id="completion_problem",
            name="Completion Problem",
            description="Provide partially solved problems to complete",
            category="new_knowledge",
            application_guidance="Give started problems that the student must complete"
        ),
        "split_attention": CltEffect(
            id="split_attention",
            name="Split Attention",
            description="Integrate related information spatially to avoid attention division",
            category="new_knowledge",
            application_guidance="Keep explanatory text near related diagrams/code"
        ),
        "modality": CltEffect(
            id="modality",
            name="Modality Effect",
            description="Use combination of presentation modes (visual + auditory)",
            category="new_knowledge",
            application_guidance="Combine textual explanations with verbal descriptions when possible"
        ),
        "redundancy": CltEffect(
            id="redundancy",
            name="Redundancy Effect",
            description="Avoid redundant information that does not add value",
            category="new_knowledge",
            application_guidance="Remove duplicate information; present each concept once clearly"
        ),
        "variability": CltEffect(
            id="variability",
            name="Variability Effect",
            description="Use multiple varied examples of the same concept",
            category="new_knowledge",
            application_guidance="Provide various examples that show different applications of the same concept"
        ),
        "isolated_elements": CltEffect(
            id="isolated_elements",
            name="Isolated Elements",
            description="Present complex elements in isolation first",
            category="new_knowledge",
            application_guidance="Introduce complex concepts one element at a time before combining them"
        ),
        "element_interactivity": CltEffect(
            id="element_interactivity",
            name="Element Interactivity",
            description="Manage the interactivity between information elements",
            category="new_knowledge",
            application_guidance="Structure the content to minimize elements that must be processed simultaneously"
        ),
        
        # Efectos para Conocimiento Previo
        "self_explanation": CltEffect(
            id="self_explanation",
            name="Self-Explanation",
            description="Encourage students to explain concepts in their own words",
            category="prior_knowledge",
            application_guidance="It includes questions that require the student to explain the 'why' and 'how'"
        ),
        "imagination": CltEffect(
            id="imagination",
            name="Imagination Effect",
            description="Ask students to imagine or visualize procedures",
            category="prior_knowledge",
            application_guidance="Request that students visualize mentally processes before executing them"
        ),
        "expertise_reversal": CltEffect(
            id="expertise_reversal",
            name="Expertise Reversal",
            description="Reduce explicit guidance for students with prior knowledge",
            category="prior_knowledge",
            application_guidance="Minimizes step-by-step instructions; provides more open-ended problems"
        ),
        "guidance_fading": CltEffect(
            id="guidance_fading",
            name="Guidance-Fading",
            description="Gradually reduce the level of guidance provided",
            category="prior_knowledge",
            application_guidance="Start with complete guidance and progressively reduce support"
        ),
        "collective_memory": CltEffect(
            id="collective_memory",
            name="Collective Memory",
            description="Leverage shared knowledge in group activities",
            category="prior_knowledge",
            application_guidance="Design activities that allow students to share prior knowledge"
        ),
        "self_management": CltEffect(
            id="self_management",
            name="Self-Management",
            description="Encourage students to manage their own learning",
            category="prior_knowledge",
            application_guidance="It gives the student autonomy in the selection of learning strategies and sequence"
        ),
        "human_movement": CltEffect(
            id="human_movement",
            name="Human Movement",
            description="Incorporate activities that involve physical movement",
            category="prior_knowledge",
            application_guidance="Suggest practical activities that require movement or physical manipulation"
        ),
        "transient_information": CltEffect(
            id="transient_information",
            name="Transient Information",
            description="Manage temporary information to avoid overload",
            category="prior_knowledge",
            application_guidance="Provide permanent references for information that disappears quickly"
        )
    }
    
    def __init__(self):
        pass
    
    def build_system_prompt(self) -> str:
        """
        Construye el system prompt base para Claude
        """
        return """You are an expert instructional designer specializing in:
- Cognitive Load Theory (CLT)
- Four-Component Instructional Design (4C/ID)
- The ARCS model of motivation (Attention, Relevance, Confidence, Satisfaction)
- Differentiated learning strategies
- Programming and software development

Your task is to generate high-quality instructional material, customized according to 
the student or group profile, applying the requested pedagogical principles.

IMPORTANT:
- Generate content in clear and professional Spanish
- Strictly follow the instructions regarding CLT effects
- Apply the ARCS model throughout the material
- Adapt the content to the student's prior knowledge level
- Use concrete and relevant programming examples
- Provide the content in the requested structured JSON format"""
    
    def build_profile_section(
        self,
        profile_type: ProfileType,
        profile_data: Union[Dict, StudentProfile, GroupProfile]
    ) -> str:
        """
        Construye la sección de perfil del prompt
        """
        if profile_type == ProfileType.INDIVIDUAL:
            return self._build_individual_profile_section(profile_data)
        else:
            return self._build_group_profile_section(profile_data)
    
    def _build_individual_profile_section(self, profile: Union[Dict, StudentProfile]) -> str:
        """
        Perfil individual del estudiante
        """
        if isinstance(profile, dict):
            summary = profile.get('profile_summary', {})
            knowledge = profile.get('knowledge_assessment', {})
        else:
            summary = profile.profile_summary.dict()
            knowledge = profile.knowledge_assessment.dict()
        
        # Scores de evaluaciones de conocimiento
        recall_initial = knowledge.get('recall', {}).get('percentage', 0)
        comprehension_initial = knowledge.get('comprehension', {}).get('percentage', 0)
        recall_final = knowledge.get('recall_final', {}).get('percentage')
        comprehension_final = knowledge.get('comprehension_final', {}).get('percentage')

        section = f"""
## STUDENT PROFILE

### General Summary
- **Motivation Level**: {summary.get('overall_motivation', 'N/A')}
- **Learning Strategies Level**: {summary.get('overall_strategies', 'N/A')}
- **Prior Knowledge Level**: {summary.get('prior_knowledge', 'N/A')}

### Knowledge Assessment Scores
- **Recall Initial**: {recall_initial}% correct
- **Comprehension Initial**: {comprehension_initial}% correct
"""
        if recall_final is not None:
            section += f"- **Recall Final**: {recall_final}% correct\n"
        if comprehension_final is not None:
            section += f"- **Comprehension Final**: {comprehension_final}% correct\n"

        section += f"""- **General Level of Knowledge**: {knowledge.get('overall_level', 'N/A')}
"""

        # Agregar scores MSLQ por dimensión si están disponibles
        mslq_scores = profile.get('mslq_scores', {}) if isinstance(profile, dict) else {}
        if mslq_scores:
            MSLQ_LABELS = {
                'intrinsic_goal_orientation': 'Intrinsic Goal Orientation',
                'extrinsic_goal_orientation': 'Extrinsic Goal Orientation',
                'task_value': 'Task Value',
                'control_beliefs': 'Control Beliefs',
                'self_efficacy': 'Self-Efficacy',
                'test_anxiety': 'Test Anxiety',
                'rehearsal': 'Review',
                'elaboration': 'Elaboration',
                'organization': 'Organization',
                'critical_thinking': 'Critical Thinking',
                'metacognitive_self_regulation': 'Metacognitive Self-Regulation',
                'time_study_environment': 'Time and Study Environment',
                'effort_regulation': 'Regulation of Effort',
                'peer_learning': 'Peer Learning',
                'help_seeking': 'Help Seeking',
            }
            section += "\n### MSLQ Scales (Motivation and Learning Strategies)\n"
            section += "*(scale 1-7: low <3.5, medium 3.5-5.4, high ≥5.5)*\n"
            for dim, data in mslq_scores.items():
                label = MSLQ_LABELS.get(dim, dim)
                avg = data.get('average', 0)
                level = data.get('level', 'N/A')
                section += f"- **{label}**: {avg:.2f} ({level})\n"

        section += f"""
### Key Strengths Identified
{self._format_list(summary.get('key_strengths', []))}

### Areas Requiring Support
{self._format_list(summary.get('areas_for_support', []))}

### Instructional Recommendations
This student will benefit from:
"""
        
        # Agregar recomendaciones específicas basadas en el perfil
        if isinstance(profile, dict):
            recommendations = profile.get('recommendations', [])
        else:
            recommendations = profile.recommendations
        
        for rec in recommendations:
            section += f"- {rec}\n"
        
        return section
    
    def _build_group_profile_section(self, profile: Union[Dict, GroupProfile]) -> str:
        """
        Perfil grupal del curso
        """
        if isinstance(profile, dict):
            summary = profile.get('group_summary', {})
            knowledge = profile.get('knowledge_averages', {})
            student_count = profile.get('student_count', 0)
        else:
            summary = profile.group_summary
            knowledge = profile.knowledge_averages
            student_count = profile.student_count
        
        section = f"""
## GROUP PROFILE

### General Information
- **NNumber of Students**: {student_count}
- **Group Characteristics**: {summary.get('group_characteristics', 'N/A')}

### Predominant Levels
- **Motivation**: {summary.get('predominant_motivation', 'N/A')}
- **Learning Strategies**: {summary.get('predominant_strategies', 'N/A')}
- **Prior Knowledge**: {summary.get('predominant_knowledge', 'N/A')}

### Average Prior Knowledge
- **Recall Average**: {knowledge.get('recall', {}).get('average', 0)}%
- **Comprehension Average**: {knowledge.get('comprehension', {}).get('average', 0)}%
- **Overall Level**: {knowledge.get('overall', {}).get('level', 'N/A')}

### Instructional Recommendations for Group Teaching
"""
        
        if isinstance(profile, dict):
            recommendations = profile.get('teaching_recommendations', [])
        else:
            recommendations = profile.teaching_recommendations
        
        for rec in recommendations:
            section += f"- {rec}\n"
        
        return section
    
    def build_learning_objectives_section(self, objectives: List[Dict]) -> str:
        """
        Construye la sección de objetivos de aprendizaje
        """
        section = """
## LEARNING OBJECTIVES OF THE COURSE

The instructional material must be aligned with the following objectives:

"""
        for i, obj in enumerate(objectives, 1):
            section += f"{i}. {obj.get('description', '')}\n"
            if obj.get('bloom_level'):
                section += f"   (Bloom's Taxonomy Level: {obj['bloom_level']})\n"
        
        return section
    
    def build_clt_effects_section(self, selected_effects: List[str]) -> str:
        """
        Construye la sección de efectos CLT a aplicar
        """
        section = """
## EFFECTS OF COGNITIVE LOAD THEORY TO BE APPLIED

You must apply the following CLT effects to the material you generate:

"""
        for effect_id in selected_effects:
            effect = self.CLT_EFFECTS.get(effect_id)
            if effect:
                section += f"""
### {effect.name}
**Description**: {effect.description}
**How to Apply**: {effect.application_guidance}

"""
        
        return section
    
    def build_arcs_section(self) -> str:
        """
        Construye la sección del modelo ARCS
        """
        return """
## APPLICATION OF THE ARCS MODEL

You must integrate the four components of the ARCS model into the material:

### A - Attention (Attention)
- Capture the student's attention from the beginning
- Use surprising or intriguing elements
- The format and style of presentation vary

### R - Relevance
- Connects the content to the student's prior experiences
- Shows practical, real-world applications
- Explains the "why" and "what for" of the topic

### C - Confidence
- Structures content with increasing difficulty
- Provides clear expectations of what will be achieved
- Offers opportunities for success

### S - Satisfaction
- Provides positive and constructive feedback
- Shows progress and achievements
- Connects learning with future goals
"""
    
    def build_differentiated_strategies_section(self, profile_type: ProfileType) -> str:
        """
        Construye la sección de estrategias diferenciadas
        """
        if profile_type == ProfileType.GROUP:
            return ""  # No se aplican estrategias individuales en material grupal
        
        return """
## DIFFERENTIATED LEARNING STRATEGIES

Based on the student's profile, incorporate specific strategies:

### If the student has weaknesses in cognitive strategies:
- Model explicitly techniques for review and elaboration
- Provide graphical organizers and conceptual maps
- Include guides for effective note-taking

### If the student has weaknesses in self-regulation:
- Include checklists of steps to follow
- Provide strategies for monitoring comprehension
- Suggest techniques for self-assessment

### If the student has high anxiety before assessments:
- Use encouraging and positive language
- Provide practice in low-risk environments
- Include strategies for managing anxiety
"""
    
    def build_material_type_instructions(
        self,
        material_type: MaterialType,
        topic: str
    ) -> str:
        """
        Construye instrucciones específicas según el tipo de material
        """
        instructions = {
            MaterialType.LEARNING_TASKS: f"""
## TYPE OF MATERIAL: LEARNING TASKS

Generate a sequence of learning tasks on: **{topic}**

### Required Characteristics:
1. **Increasing Difficulty**: Design 3-5 tasks that progressively increase in complexity.
2. **Authentic Scenarios**: Use realistic programming situations.
3. **Explanation of the WHY and WHAT FOR**: Clearly explain the relevance of each task.
4. **Elements of 4C/ID**:

- Meaningful and complete tasks

- Presentation in an authentic context

- Gradually decreasing support

### JSON Output Format:
```json
{{
  "tasks": [
    {{
      "task_number": 1,
      "title": "Task Title",
      "difficulty_level": "basic|intermediate|advanced",
      "description": "Complete description of the problem",
      "context": "Real-world scenario",
      "why_relevant": "Why this task is important",
      "expected_outcome": "What will be achieved",
      "estimated_time": "time in minutes",
      "support_level": "high|medium|low",
      "arcs_integration": {{
        "attention": "How to capture attention",
        "relevance": "Why it is relevant",
        "confidence": "How it builds confidence",
        "satisfaction": "How it generates satisfaction"
      }}
    }}
  ]
}}
```
""",
            MaterialType.SUPPORT_INFO: f"""
## TYPE OF MATERIAL: SUPPORT INFORMATION

Generate theoretical support information on: **{topic}**

### Required Characteristics:
1. **Fundamental Theory**: Basic concepts and theoretical foundations
2. **Clear Explanations**: Accessible language adapted to the student's level
3. **Illustrative Examples**: Concrete examples of each concept
4. **Logical Organization**: Structure that facilitates understanding

### JSON Output Format:
```json
{{
  "sections": [
    {{
      "title": "Section title",
      "order": 1,
      "content": "Complete explanatory content",
      "key_concepts": ["concept1", "concept2"],
      "examples": [
        {{
          "description": "Example Description",
          "code": "code if applicable"
        }}
      ],
      "arcs_integration": {{
        "attention": "How to capture attention",
        "relevance": "Why it is relevant",
        "confidence": "How it builds confidence",
        "satisfaction": "How it generates satisfaction"
      }}
    }}
  ],
  "summary": "General summary of the content",
  "key_takeaways": ["key takeaway 1", "key takeaway 2"]
}}
```
""",
            MaterialType.PROCEDURAL_INFO: f"""
## TYPE OF MATERIAL: PROCEDURAL INFORMATION

Generate procedural information (examples, guides, models) on: **{topic}**

### Required Characteristics:
1. **Isomorphic Examples**: Examples with similar structure but different context
2. **Guiding Questions**: Questions that guide the thinking process
3. **Models and Maps**: Visual representations of the knowledge
4. **Step-by-Step Procedures**: When appropriate

### JSON Output Format:
```json
{{
  "worked_examples": [
    {{
      "title": "Example Title",
      "problem": "Problem Description",
      "solution_steps": [
        {{
          "step_number": 1,
          "description": "What is done",
          "explanation": "Why it is done",
          "code": "code if applicable"
        }}
      ],
      "key_insights": ["insight 1", "insight 2"]
    }}
  ],
  "guiding_questions": [
    "A question that encourages reflection?",
    "A question that connects concepts?"
  ],
  "conceptual_models": [
    {{
      "title": "Example Model Title",
      "description": "Description of the mental model",
      "visual_representation": "Description of how to visualize it"
    }}
  ]
}}
```
""",
            MaterialType.VERBAL_PROTOCOLS: f"""
## TYPE OF MATERIAL: VERBAL PROTOCOLS

Generate a verbal protocol (think-aloud) about: **{topic}**

### Required Characteristics:
1. **Expert Perspective**: From the viewpoint of an experienced programmer
2. **Focus on HOW and WHY**: Explain the thought process
3. **Naturalness**: As if you were thinking out loud
4. **Process Transparency**: Show also doubts and decisions

### JSON Output Format:
```json
{{
  "protocol_title": "Protocol Title",
  "scenario": "Scenario/Problem Description",
  "think_aloud_transcript": "Complete transcript of the think-aloud process, including:\n- Initial analysis of the problem\n- Considerations and alternatives\n- Decisions made and reasons\n- Implementation process\n- Reflections during the process\n- Solution validation",
  "key_thinking_patterns": [
    "Thinking pattern 1",
    "Thinking pattern 2"
  ],
  "teaching_points": [
    "Teaching Point 1",
    "Teaching Point 2"
  ]
}}
```
""",
            MaterialType.EXAMPLE: f"""
## TYPE OF MATERIAL: REAL EXAMPLE

Generate a real and complete example about: **{topic}**

### Required Characteristics:
1. **Completeness**: Functional and complete example
2. **Professional Quality**: Code that follows best practices
3. **Explanatory Comments**: Well-commented code
4. **Practical Application**: Real-world problem

### JSON Output Format:
```json
{{
  "example_title": "Example Title",
  "description": "Description of the problem that is solved",
  "use_case": "Real-world use case",
  "code": "Complete code with comments",
  "explanation": "Detailed explanation of how it works",
  "key_concepts_demonstrated": ["concept 1", "concept 2"],
  "possible_variations": [
    "Variation 1 of the example",
    "Variation 2 of the example"
  ],
  "common_mistakes": [
    "Common error 1 and how to avoid it",
    "Common error 2 and how to avoid it"
  ]
}}
```
"""
        }
        
        return instructions.get(material_type, "")
    
    def build_output_format_section(self) -> str:
        """
        Construye la sección de formato de salida
        """
        return """
## JSON OUTPUT FORMAT

IMPORTANT: 
- Return ONLY the requested JSON, no additional text before or after
- DO NOT include markdown code blocks (```)
- The JSON must be valid and parseable
- Use Spanish for all content
- Be specific and detailed in the explanations
"""
    
    def build_complete_prompt(self, request: MaterialGenerationRequest) -> tuple[str, str]:
        """
        Construye el prompt completo para Claude API
        Returns: (system_prompt, user_prompt)
        """
        system_prompt = self.build_system_prompt()
        
        user_prompt = f"""
{self.build_profile_section(request.profile_type, request.profile_data)}

{self.build_learning_objectives_section([obj.dict() for obj in request.learning_objectives])}

{self.build_clt_effects_section(request.selected_clt_effects)}

{self.build_arcs_section()}

{self.build_differentiated_strategies_section(request.profile_type)}

{self.build_material_type_instructions(request.material_type, request.topic)}

{self.build_output_format_section()}
"""
        
        if request.additional_context:
            user_prompt += f"""
## ADDITIONAL CONTEXT

{request.additional_context}
"""
        type_m =request.material_type
        carpeta_destino = "C:/Projects/CLT4BP/docs/"
        prefijo = "backup_"
        nombre_archivo = "material.json"
        if not os.path.exists(carpeta_destino):
            os.makedirs(carpeta_destino)
        ruta_completa = os.path.join(carpeta_destino, f"{prefijo}{nombre_archivo}")

        # 4. Guardar la variable en el archivo JSON
        with open(ruta_completa, 'w', encoding='utf-8') as f:
            json.dump(user_prompt, f, indent=4, ensure_ascii=False)
        return system_prompt, user_prompt
    
    def _format_list(self, items: List[str]) -> str:
        """
        Formatea una lista de items
        """
        if not items:
            return "- None identified\n"
        return "\n".join([f"- {item}" for item in items]) + "\n"